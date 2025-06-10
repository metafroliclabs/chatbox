<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Models\ChatMessage;

class BaseService
{
    /**
     * Indicates if pagination is enabled.
     *
     * @var bool
     */
    protected $pagination;

    /**
     * Number of records per page for pagination.
     *
     * @var int
     */
    protected $per_page;

    /**
     * BaseService constructor.
     * Initializes pagination configuration.
     */
    public function __construct()
    {
        $pagination = config('chat.pagination', true);
        $requestPerPage = config('chat.per_page', 25);

        $this->pagination = $pagination;
        $this->per_page = $requestPerPage;
    }

    /**
     * Get the expression for the full name column for SQL queries.
     *
     * If multiple name columns are configured, returns a DB raw
     * CONCAT expression to combine them.
     *
     * @return \Illuminate\Database\Query\Expression|string
     */
    protected function getNameColumn()
    {
        $cols = config('chat.user.name_cols', []);
        if (count($cols) > 1) {
            $str = implode(", ' ', ", $cols);
            return DB::raw("CONCAT($str)");
        }
        return $cols[0];
    }

    /**
     * Retrieve the full name of a user based on configured name columns.
     *
     * @param  object|null  $user  The user object.
     * @return string  The full name composed from configured columns.
     */
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

    /**
     * Log a chat activity message.
     *
     * Can log a single message or multiple messages to the chat as activity entries.
     * Uses the current authenticated user ID if not provided.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $chat  The chat model instance.
     * @param  string|array  $message  The activity message(s) to log.
     * @param  int|null  $userId  Optional user ID. Defaults to authenticated user.
     * @return void
     */
    protected function logActivity($chat, string|array $message, ?int $userId = null): void
    {
        if (!config('chat.enable_activity_messages', true)) {
            return;
        }

        $userId = $userId ?? auth()->id();

        if (is_array($message)) {
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
            $chat->messages()->create([
                'type' => ChatMessage::ACTIVITY,
                'user_id' => $userId,
                'message' => $message,
            ]);
        }
    }
}
