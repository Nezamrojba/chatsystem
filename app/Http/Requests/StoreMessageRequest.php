<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    /**
     * Authorize request - user must be authenticated.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating message.
     */
    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'exists:conversations,id'],
            'body' => ['required_without:voice_note', 'nullable', 'string'],
            'type' => ['required', 'string', Rule::in(['text', 'voice', 'image', 'file'])],
            'voice_note' => ['required_if:type,voice', 'file', 'mimes:mp3,wav,ogg,webm', 'max:10240'],
            'voice_note_duration' => ['required_if:type,voice', 'nullable', 'integer', 'min:1'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
