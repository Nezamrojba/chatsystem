<?php

namespace App\Http\Controllers\Api;

use App\Events\MessagesRead;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    /**
     * Get user's conversations.
     * Uses caching to reduce database queries
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $conversations = CacheService::getConversations($userId, function () use ($userId) {
            return Conversation::forUser($userId)
                ->with(['latestMessage.user', 'users'])
                ->orderBy('last_message_at', 'desc')
                ->paginate(20);
        });

        return ConversationResource::collection($conversations);
    }

    /**
     * Create new conversation.
     */
    public function store(StoreConversationRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $userIds = $request->user_ids;

        // Add current user to participants
        if (!in_array($userId, $userIds)) {
            $userIds[] = $userId;
        }

        // Check if private conversation already exists
        if ($request->type === 'private' && count($userIds) === 2) {
            $existing = Conversation::private()
                ->whereHas('users', fn($q) => $q->where('users.id', $userIds[0]))
                ->whereHas('users', fn($q) => $q->where('users.id', $userIds[1]))
                ->withCount('users')
                ->having('users_count', '=', 2)
                ->first();

            if ($existing) {
                return response()->json(new ConversationResource($existing->load(['users', 'latestMessage'])), 200);
            }
        }

        // Create conversation
        $conversation = Conversation::create([
            'title' => $request->title,
            'type' => $request->type,
            'created_by' => $userId,
        ]);

        // Attach users
        $conversation->users()->attach($userIds, ['joined_at' => now()]);

        // Invalidate cache for all participants
        foreach ($userIds as $id) {
            CacheService::invalidateConversations($id);
        }

        return response()->json(new ConversationResource($conversation->load(['users', 'latestMessage'])), 201);
    }

    /**
     * Get conversation details.
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        // Verify user is participant
        if (!$conversation->users->contains($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(new ConversationResource(
            $conversation->load(['users', 'creator', 'latestMessage'])
        ));
    }

    /**
     * Update conversation.
     */
    public function update(UpdateConversationRequest $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        // Verify user is participant
        if (!$conversation->users->contains($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation->update($request->validated());

        return response()->json(new ConversationResource($conversation->load(['users', 'latestMessage'])));
    }

    /**
     * Delete conversation.
     */
    public function destroy(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        // Verify user is creator or participant
        if ($conversation->created_by !== $userId && !$conversation->users->contains($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation->delete();

        return response()->json(['message' => 'Conversation deleted'], 200);
    }

    /**
     * Get conversation messages.
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse|AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        // Verify user is participant
        if (!$conversation->users->contains($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Mark as read
        $conversation->users()->updateExistingPivot($userId, ['read_at' => now()]);
        
        // Reload conversation to get updated pivot data
        $conversation->load('users');

        // Invalidate cache
        CacheService::invalidateConversations($userId);
        CacheService::invalidateMessages($conversation->id);

        // Broadcast read event to notify other users
        broadcast(new MessagesRead($conversation, $userId))->toOthers();

        return MessageResource::collection($messages);
    }
}
