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
        $minUsers = config('chat.min_group_users', 2);
        $maxUsers = config('chat.max_group_users', 9);

        return [
            'name' => 'required|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'users' => ['required', 'array', "min:$minUsers", "max:$maxUsers"],
            'users.*' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        $minUsers = config('chat.min_group_users', 2);
        $maxUsers = config('chat.max_group_users', 9);

        return [
            'users.min' => "Group must have at least $minUsers members",
            'users.max' => "Group can have max $maxUsers members",
        ];
    }
}
