<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform conversation resource into array.
     */
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;
        $readAt = null;
        $readAtCarbon = null;
        $otherUserReadAt = null;
        $unreadCount = 0;
        
        // Get read_at for current user and other user
        if ($userId && $this->relationLoaded('users')) {
            foreach ($this->users as $user) {
                if ($user->id === $userId) {
                    // Get raw read_at for query calculation
                    $readAtCarbon = $user->pivot->read_at 
                        ? (is_string($user->pivot->read_at) 
                            ? \Carbon\Carbon::parse($user->pivot->read_at) 
                            : $user->pivot->read_at)
                        : null;
                    // Format read_at as ISO string for API response
                    $readAt = $readAtCarbon ? $readAtCarbon->toIso8601String() : null;
                } else {
                    // Other user's read_at (for read receipts)
                    // Format as ISO string for consistent parsing on frontend
                    $otherUserReadAt = $user->pivot->read_at 
                        ? (is_string($user->pivot->read_at) 
                            ? \Carbon\Carbon::parse($user->pivot->read_at)->toIso8601String() 
                            : $user->pivot->read_at->toIso8601String())
                        : null;
                }
            }
        }

        // Calculate unread count: messages from other users after read_at
        if ($userId) {
            $query = $this->messages()
                ->where('user_id', '!=', $userId);
            
            if ($readAtCarbon) {
                // Use Carbon instance for proper date comparison
                $query->where('created_at', '>', $readAtCarbon);
            }
            
            $unreadCount = $query->count();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'created_by' => $this->created_by,
            'last_message_at' => $this->last_message_at,
            'read_at' => $readAt,
            'other_user_read_at' => $otherUserReadAt,
            'unread_count' => $unreadCount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'messages_count' => $this->whenCounted('messages'),
        ];
    }
}
