<?php

namespace Metafroliclabs\LaravelChat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
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
            'message' => 'required_without:attachments',
            'reply_to' => 'nullable|exists:chat_messages,id', 
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'required|file|max:' . config('chat.file.max_size'),
        ];
    }
}
