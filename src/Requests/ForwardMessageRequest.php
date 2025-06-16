<?php

namespace Metafroliclabs\LaravelChat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForwardMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'messages' => 'required|array',
            'messages.*' => 'required|exists:chat_messages,id',
            'chats' => 'required|array',
            'chats.*' => 'required|exists:chats,id',
        ];
    }
}
