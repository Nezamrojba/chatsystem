<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Batch operations controller
 * Allows multiple operations in single API call to reduce requests
 */
class BatchController extends Controller
{
    /**
     * Batch fetch conversations and messages
     * Reduces API calls by combining multiple requests
     */
    public function fetch(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'conversations' => ['sometimes', 'boolean'],
            'messages' => ['sometimes', 'array'],
            'messages.*.conversation_id' => ['required_with:messages', 'exists:conversations,id'],
            'messages.*.page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = [];

        // Fetch conversations if requested
        if ($request->boolean('conversations')) {
            $conversations = CacheService::getConversations($userId, function () use ($userId) {
                return Conversation::forUser($userId)
                    ->with(['latestMessage.user', 'users'])
                    ->orderBy('last_message_at', 'desc')
                    ->paginate(20);
            });

            $response['conversations'] = ConversationResource::collection($conversations);
        }

        // Fetch messages for multiple conversations
        if ($request->has('messages')) {
            $messagesData = [];
            foreach ($request->input('messages', []) as $messageRequest) {
                $conversationId = $messageRequest['conversation_id'];
                $page = $messageRequest['page'] ?? 1;

                // Verify user is participant
                $conversation = Conversation::find($conversationId);
                if (!$conversation || !$conversation->users->contains($userId)) {
                    continue;
                }

                $messages = CacheService::getMessages($conversationId, $page, function () use ($conversation) {
                    return $conversation->messages()
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->paginate(50);
                });

                $messagesData[$conversationId] = MessageResource::collection($messages);
            }

            $response['messages'] = $messagesData;
        }

        return response()->json($response);
    }
}

