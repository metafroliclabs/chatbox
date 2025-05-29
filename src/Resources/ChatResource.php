<?php

namespace Metafroliclabs\LaravelChat\Resources;

use Metafroliclabs\LaravelChat\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->users->where('id', '!=', auth()->id())->first();
        $private = ($this->type === Chat::PRIVATE) ? true : false;

        // unread messages count
        $count = $this->messages
        ->where('user_id', '!=', auth()->id())
        ->reject(fn($message) => $message->views->where('user_id', auth()->id())->isNotEmpty())
        ->count();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->when($private, $user->full_name, $this->name),
            'image' => $this->when($private, $user->avatar, $this->image),
            'unread' => $count,
            'last_message' => new ShortMessageResource($this->messages->last()),
            'setting' => $this->setting
        ];
    }
}
