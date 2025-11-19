<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\CacheService;
use App\Services\CompressionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Get messages for conversation.
     */
    public function index(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        $userId = $request->user()->id;
        
        // Verify user is participant
        if (!$conversation->users->contains($userId)) {
            abort(403, 'Unauthorized');
        }

        // Use caching to reduce database queries
        $page = $request->get('page', 1);
        
        $messages = CacheService::getMessages($conversation->id, $page, function () use ($conversation) {
            return $conversation->messages()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(50);
        });

        return MessageResource::collection($messages);
    }

    /**
     * Create new message.
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $conversation = Conversation::findOrFail($request->conversation_id);

        // Verify user is participant
        if (!$conversation->users->contains($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = [
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'type' => $request->type,
            'body' => $request->body,
            'metadata' => $request->metadata ?? [],
        ];

        // Handle voice note upload with compression
        if ($request->type === 'voice' && $request->hasFile('voice_note')) {
            $file = $request->file('voice_note');
            $path = $file->store('voice_notes', 'public');
            
            // Compress if needed (optimizes bandwidth)
            $optimizedPath = CompressionService::compressVoiceNote($file, $path);
            
            $data['voice_note_path'] = $optimizedPath;
            $data['voice_note_duration'] = $request->voice_note_duration;
            $data['metadata'] = array_merge($data['metadata'], [
                'phone' => $request->user()->phone ?? null,
                'file_size' => $file->getSize(),
            ]);
        }

        $message = Message::create($data);

        // Update conversation last message timestamp
        $conversation->update(['last_message_at' => now()]);

        // Invalidate cache for all conversation participants
        foreach ($conversation->users as $user) {
            CacheService::invalidateConversations($user->id);
        }
        CacheService::invalidateMessages($conversation->id);

        // Broadcast message event
        broadcast(new MessageSent($message->load('user')))->toOthers();

        return response()->json(new MessageResource($message->load('user')), 201);
    }

    /**
     * Get message details.
     */
    public function show(Request $request, Message $message): JsonResponse
    {
        $userId = $request->user()->id;
        
        // Verify user is conversation participant
        if (!$message->conversation->users->contains($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(new MessageResource($message->load(['user', 'conversation'])));
    }

    /**
     * Update message.
     */
    public function update(UpdateMessageRequest $request, Message $message): JsonResponse
    {
        $userId = $request->user()->id;
        
        // Verify user owns message
        if ($message->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->update([
            'body' => $request->body,
        ]);

        $message->markAsEdited();

        return response()->json(new MessageResource($message->load('user')));
    }

    /**
     * Delete message.
     */
    public function destroy(Request $request, Message $message): JsonResponse
    {
        $userId = $request->user()->id;
        
        // Verify user owns message or is conversation participant
        if ($message->user_id !== $userId && !$message->conversation->users->contains($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete voice note file if exists
        if ($message->voice_note_path && Storage::disk('public')->exists($message->voice_note_path)) {
            Storage::disk('public')->delete($message->voice_note_path);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted'], 200);
    }

    /**
     * Upload voice note for existing message.
     */
    public function uploadVoice(Request $request, Message $message): JsonResponse
    {
        $userId = $request->user()->id;
        
        // Verify user owns message
        if ($message->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        request()->validate([
            'voice_note' => ['required', 'file', 'mimes:mp3,wav,ogg,webm', 'max:10240'],
            'voice_note_duration' => ['required', 'integer', 'min:1'],
        ]);

        // Delete old voice note if exists
        if ($message->voice_note_path && Storage::disk('public')->exists($message->voice_note_path)) {
            Storage::disk('public')->delete($message->voice_note_path);
        }

        // Store and compress voice note
        $file = request()->file('voice_note');
        $path = $file->store('voice_notes', 'public');
        $optimizedPath = CompressionService::compressVoiceNote($file, $path);

        $message->update([
            'type' => 'voice',
            'voice_note_path' => $optimizedPath,
            'voice_note_duration' => request()->voice_note_duration,
            'metadata' => array_merge($message->metadata ?? [], [
                'phone' => $request->user()->phone ?? null,
                'file_size' => $file->getSize(),
            ]),
        ]);

        // Invalidate cache
        CacheService::invalidateMessages($message->conversation_id);

        return response()->json(new MessageResource($message->load('user')));
    }
}
