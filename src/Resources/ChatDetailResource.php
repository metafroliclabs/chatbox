<?php

namespace Metafroliclabs\LaravelChat\Resources;

use Metafroliclabs\LaravelChat\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafroliclabs\LaravelChat\Models\ChatMessage;

class ChatDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authId = auth()->id();
        $user = $this->users->where('id', '!=', $authId)->first();
        $authUser = $this->users->where('id', $authId)->first();
        $otherUsers = $this->users->where('id', '!=', $authId)->values();
        $private = ($this->type === Chat::PRIVATE) ? true : false;

        $fullname = $this->getUserFullname($user);
        $avatar = $this->getUserAvatar($user);
        $count = $this->getUnreadMessagesCount($user);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->when($private, $fullname, $this->name),
            'image' => $this->when($private, $avatar, $this->image),
            'unread' => $count,
            'bg_notification' => $authUser->pivot->bg_notification,
            'created_at' => $this->created_at,
            'setting' => $this->setting,
            'created_by' => $this->createdBy,
            'users' => [
                'me' => $authUser,
                'others' => $otherUsers,
            ],
        ];
    }

    protected function getUserFullname($user)
    {
        $fullname = "";
        $cols = config('chat.user.name_cols', []);

        if ($user && !empty($cols)) {
            $fullname = collect($cols)
                ->map(fn($col) => $user->{$col} ?? '')
                ->filter() // remove null or empty values
                ->implode(' ');
        }

        return $fullname;
    }

    protected function getUserAvatar($user)
    {
        $avatar = null;
        $image_col = config('chat.user.image_col', 'avatar');
        $image_url = config('chat.user.enable_image_url', true);

        if ($user) {
            $image_val = $user->{$image_col} ?? null;
            if ($image_val) {
                $avatar = $image_url ? asset($image_val) : $image_val;
            }
        }

        return $avatar;
    }

    protected function getUnreadMessagesCount($user)
    {
        $count = $this->messages
        ->where('user_id', '!=', auth()->id())
        ->where('type', ChatMessage::MESSAGE)
        ->where('created_at', '>=', $user->pivot?->created_at)
        ->where('created_at', '>=', $user->pivot?->cleared_at)
        ->reject(fn($message) => $message->views->where('user_id', auth()->id())->isNotEmpty())
        ->count();

        return $count ?? 0;
    }
}
