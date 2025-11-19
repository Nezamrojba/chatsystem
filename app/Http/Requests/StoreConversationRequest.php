<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationRequest extends FormRequest
{
    /**
     * Authorize request - user must be authenticated.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating conversation.
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['private', 'group'])],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ];
    }
}
