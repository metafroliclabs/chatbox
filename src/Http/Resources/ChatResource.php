<?php

namespace Metafroliclabs\LaravelChat\Http\Resources;

use App\Models\Chat;
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

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->when($private, $user->first_name . " " . $user->last_name, $this->name),
            'shop_name' => $this->when($private, $user->shop?->name, null),
            'image' => $this->when($private, $user->avatar, $this->image),
            'unread' => $this->messages()->whereNull('read_at')->whereNot('user_id', auth()->id())->count(),
            'last_message' => new MessageResource($this->messages->last()),
        ];
    }
}
