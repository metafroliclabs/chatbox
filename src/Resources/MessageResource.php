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
            'type' => $this->type,
            'chat_id' => $this->chat_id,
            'message' => $this->deleted_at ? "This message was deleted." : $this->message,
            'is_updated' => $this->is_updated,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'user' => $this->user,
            'repliedTo' => new ShortMessageResource($this->repliedTo),
            'attachment' => $this->attachment,
            'reactions' => $this->reactions,
            'views' => $this->views,
        ];
    }
}
