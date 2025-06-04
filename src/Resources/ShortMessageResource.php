<?php

namespace Metafroliclabs\LaravelChat\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortMessageResource extends JsonResource
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
            'message' => $this->deleted_at ? "This message was deleted." : $this->message,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'attachment' => $this->attachment,
            'user' => $this->user,
        ];
    }
}
