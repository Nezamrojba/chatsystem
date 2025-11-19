<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new event instance.
     */
    public function __construct(
        public Conversation $conversation,
        public int $userId
    ) {
        //
    }

    /**
     * Get channels to broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation->id),
        ];
    }

    /**
     * Get data to broadcast.
     */
    public function broadcastWith(): array
    {
        // Reload conversation to get updated pivot data
        $conversation = $this->conversation->load('users');
        
        // Get read_at from pivot
        $readAt = null;
        foreach ($conversation->users as $user) {
            if ($user->id === $this->userId) {
                $readAt = $user->pivot->read_at;
                break;
            }
        }
        
        return [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->userId,
            'read_at' => $readAt ? (is_string($readAt) 
                ? \Carbon\Carbon::parse($readAt)->toIso8601String() 
                : $readAt->toIso8601String()) 
                : null,
        ];
    }

    /**
     * Broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'messages.read';
    }
}

