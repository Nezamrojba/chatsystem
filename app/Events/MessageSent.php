<?php

namespace App\Events;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new event instance.
     */
    public function __construct(
        public Message $message
    ) {
        //
    }

    /**
     * Get channels to broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * Get data to broadcast.
     */
    public function broadcastWith(): array
    {
        return (new MessageResource($this->message))->toArray(request());
    }

    /**
     * Broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
