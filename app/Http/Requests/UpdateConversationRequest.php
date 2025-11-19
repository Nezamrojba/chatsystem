<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConversationRequest extends FormRequest
{
    /**
     * Authorize request - user must be authenticated.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating conversation.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', Rule::in(['private', 'group'])],
        ];
    }
}
