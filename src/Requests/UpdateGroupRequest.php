<?php

namespace Metafroliclabs\LaravelChat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
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
            'name' => 'required|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'can_add_users' => 'nullable|in:0,1',
            'can_send_messages' => 'nullable|in:0,1',
            'can_update_settings' => 'nullable|in:0,1',
        ];
    }
}
