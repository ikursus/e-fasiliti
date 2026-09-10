<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * One assistant turn. The cap bounds a single request and matches the
     * composer limit in the chat interface.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:4000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Mesej tidak boleh kosong.',
            'message.max' => 'Mesej melebihi had 4,000 aksara.',
        ];
    }
}
