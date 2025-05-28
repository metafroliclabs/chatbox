<?php

namespace Metafroliclabs\LaravelChat\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->first_name . " " . $this->user->last_name,
            'message' => $this->message,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            // 'attachment' => $this->attachment,
        ];
    }
}
