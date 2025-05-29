<?php

namespace Metafroliclabs\LaravelChat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupRequest extends FormRequest
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
            'users' => 'required|array|min:2|max:9',
            'users.*' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'users.min' => 'Group must have at least 2 members',
            'users.max' => 'Group can have max 9 members',
        ];
    }
}
