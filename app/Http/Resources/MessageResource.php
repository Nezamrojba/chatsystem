<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform message resource into array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'user_id' => $this->user_id,
            'body' => $this->body,
            'type' => $this->type,
            'voice_note_path' => $this->voice_note_path,
            'voice_note_duration' => $this->voice_note_duration,
            'is_voice_note' => $this->isVoiceNote(),
            'metadata' => $this->metadata,
            'is_edited' => $this->is_edited,
            'edited_at' => $this->edited_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'conversation' => new ConversationResource($this->whenLoaded('conversation')),
        ];
    }
}
