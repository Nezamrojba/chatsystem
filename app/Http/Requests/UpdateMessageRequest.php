<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
{
    /**
     * Authorize request - user must be authenticated.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating message.
     */
    public function rules(): array
    {
        return [
            'body' => ['sometimes', 'required', 'string'],
        ];
    }
}
