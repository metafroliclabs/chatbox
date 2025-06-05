<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Models\ChatMessage;

class BaseService
{
    protected $pagination;
    protected $per_page;

    public function __construct()
    {
        $pagination = config('chat.pagination', true);
        $requestPerPage = config('chat.per_page', 25);

        $this->pagination = $pagination;
        $this->per_page = $requestPerPage;
    }

    protected function getNameColumn()
    {
        $cols = config('chat.user.name_cols', []);
        if (count($cols) > 1) {
            $str = implode(", ' ', ", $cols);
            return DB::raw("CONCAT($str)");
        }
        return $cols[0];
    }

    protected function getFullName($user)
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

    protected function logActivity($chat, string|array $message, ?int $userId = null): void
    {
        if (!config('chat.enable_activity_messages', true)) {
            return;
        }

        $userId = $userId ?? auth()->id();

        if (is_array($message)) {
            // Support multiple activity messages
            $messages = array_map(fn($msg) => [
                'type' => ChatMessage::ACTIVITY,
                'chat_id' => $chat->id,
                'user_id' => $userId,
                'message' => $msg,
                'created_at' => now(),
                'updated_at' => now(),
            ], $message);

            ChatMessage::insert($messages);

        } else {
            // Single activity message
            $chat->messages()->create([
                'type' => ChatMessage::ACTIVITY,
                'user_id' => $userId,
                'message' => $message,
            ]);
        }
    }
}