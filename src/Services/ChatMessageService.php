<?php

namespace Metafroliclabs\LaravelChat\Services;

use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Events\MessageSent;
use Metafroliclabs\LaravelChat\Exceptions\ChatException;
use Metafroliclabs\LaravelChat\Models\Chat;
use Metafroliclabs\LaravelChat\Models\ChatMessage;
use Metafroliclabs\LaravelChat\Models\ChatMessageView;
use Metafroliclabs\LaravelChat\Services\Core\BaseService;
use Metafroliclabs\LaravelChat\Services\Core\FileService;
use Metafroliclabs\LaravelChat\Policies\ChatAccessPolicy;

class ChatMessageService extends BaseService
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        parent::__construct();
        $this->fileService = $fileService;
    }

    public function getChatMessages($chat)
    {
        $authId = auth()->id();
        $user = $chat->users()->where('user_id', $authId)->first();

        $query = $chat->messages()
            ->whereDoesntHave('deletions', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->latest();

        if ($user) {
            if ($user->pivot?->created_at) {
                $query->where('created_at', '>=', $user->pivot->created_at);
            }

            if ($user->pivot?->cleared_at) {
                $query->where('created_at', '>=', $user->pivot->cleared_at);
            }
        }

        $messages = $this->pagination
            ? $query->paginate($this->per_page)
            : $query->get();

        // Get IDs of messages not yet viewed by the current user and not sent by the user
        $unseenMessages = $messages->filter(function ($msg) use ($authId) {
            return is_null($msg->deleted_at) // only mark not-deleted messages
                && $msg->user_id !== $authId
                && $msg->type !== ChatMessage::ACTIVITY
                && !$msg->views->contains('user_id', $authId);
        });

        // Prepare insert data
        $now = now();
        $viewData = $unseenMessages->map(function ($msg) use ($authId, $now) {
            return [
                'chat_message_id' => $msg->id,
                'user_id' => $authId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        if (ChatAccessPolicy::isFeatureEnabled('views')) {
            // Insert unseen view records
            if (!empty($viewData)) {
                ChatMessageView::insert($viewData);
            }
            $messages->load('views');
        }

        return $messages;
    }

    public function sendMessage($chat, $request)
    {
        $authId = auth()->id();
        $authUser = auth()->user();
        $setting = $chat->setting;
        $replyId = null;

        $users = $chat->users()->where('user_id', '!=', $authId)->where('bg_notification', true)->get();

        if ($chat->type === Chat::GROUP && !$setting->can_send_messages && $authUser->pivot->role !== Chat::ADMIN) {
            throw new ChatException("Only admins are allowed to send messages");
        }

        if ($request->reply_id) {
            $message = $chat->messages()->findOrFail($request->reply_id);

            if ($message->type === ChatMessage::MESSAGE && is_null($message->deleted_at)) {
                $replyId = $request->reply_id;
            }
        }

        $messages = array();
        DB::beginTransaction();
        if ($request->attachments) {
            $uploaded = $this->fileService->uploadMultipleFiles($request->attachments);
            $images = $uploaded['data'];

            foreach ($images as $key => $image) {
                $message = $chat->messages()->create([
                    'type' => ChatMessage::MESSAGE,
                    'user_id' => $authId,
                    'replied_to_message_id' => $replyId,
                    'is_updated' => 0,
                    'is_forwarded' => 0,
                ]);
                $message->attachment()->create($image);
                $messages[] = $message;
            }
        }

        if ($request->message) {
            $message = $chat->messages()->create([
                'type' => ChatMessage::MESSAGE,
                'user_id' => $authId,
                'message' => $request->message,
                'replied_to_message_id' => $replyId,
                'is_updated' => 0,
                'is_forwarded' => 0,
            ]);
            $messages[] = $message;
        }
        DB::commit();

        if (config('chat.message.dispatch_events', true)) {
            // Calling event for end-user
            event(new MessageSent($chat, $messages, $authUser, $users));
        }

        return $messages;
    }

    public function forwardMessages($chat, $request)
    {
        $authId = auth()->id();
        $authUser = auth()->user();
        $allForwardedMessages = [];

        // Fetch all messages to forward (with necessary relations)
        $originalMessages = $chat->messages()
            ->where('type', ChatMessage::MESSAGE)
            ->whereNull('deleted_at')
            ->whereIn('id', $request->messages)
            ->get();

        DB::beginTransaction();

        $chats = Chat::withUser($authId)
            ->whereIn('id', $request->chats)
            ->get();

        foreach ($chats as $chatData) {
            $forwardedMessages = [];
            // Check if the user is allowed to send messages in this chat
            $setting = $chatData->setting;
            $authPivot = $chatData->users()->where('user_id', $authId)->first();

            if (
                $chatData->type === Chat::GROUP &&
                !$setting->can_send_messages &&
                $authPivot->pivot->role !== Chat::ADMIN
            ) {
                continue; // Skip this chat if not allowed
            }

            // Get users for event
            $chatUsers = $chatData->users()->where('user_id', '!=', $authId)->where('bg_notification', true)->get();

            foreach ($originalMessages as $original) {
                // Create the new forwarded message
                $newMessage = $chatData->messages()->create([
                    'user_id' => $authId,
                    'message' => $original->message,
                    'is_forwarded' => true,
                ]);

                // Copy attachment if it exists
                if ($original->attachment) {
                    $newMessage->attachment()->create([
                        'file' => $original->attachment->file,
                        'ext' => $original->attachment->ext,
                        'type' => $original->attachment->type,
                    ]);
                }

                $forwardedMessages[] = $newMessage;
                $allForwardedMessages[] = $newMessage;
            }

            // Fire event for this chat
            event(new MessageSent($chatData, $forwardedMessages, $authUser, $chatUsers));
        }
        DB::commit();

        return $allForwardedMessages;
    }

    public function getMessageLikes($chat, $mid)
    {
        $message = $chat->messages()
            ->where('type', ChatMessage::MESSAGE)
            ->whereNull('deleted_at')
            ->findOrFail($mid);

        $query = $message->reactions()->latest();

        return $this->pagination
            ? $query->paginate($this->per_page)
            : $query->get();
    }

    public function toggleLike($chat, $mid)
    {
        $message = $chat->messages()
            ->where('type', ChatMessage::MESSAGE)
            ->whereNull('deleted_at')
            ->findOrFail($mid);

        $isLiked = $message->reactions()->where('user_id', auth()->id())->first();

        if ($isLiked) {
            $isLiked->delete();
            return "Unliked successfully";
        }

        $message->reactions()->create(['user_id' => auth()->id()]);
        return "Liked successfully";
    }

    public function getMessageViews($chat, $mid)
    {
        $message = $chat->messages()
            ->where('type', ChatMessage::MESSAGE)
            ->whereNull('deleted_at')
            ->findOrFail($mid);

        $query = $message->views()->latest();

        return $this->pagination
            ? $query->paginate($this->per_page)
            : $query->get();
    }

    public function viewMessage($chat, $mid)
    {
        $message = $chat->messages()
            ->where('type', ChatMessage::MESSAGE)
            ->whereNull('deleted_at')
            ->findOrFail($mid);

        $view = $message->views()->firstOrCreate(['user_id' => auth()->id()]);

        return $view;
    }

    public function updateMessage($request, $chat, $mid)
    {
        $enable_update_time = config('chat.message.enable_update_time', true);
        $update_time_limit = config('chat.message.update_time_limit', 60);

        $authId = auth()->id();
        $message = $chat->messages()
            ->where('type', ChatMessage::MESSAGE)
            ->whereNull('deleted_at')
            ->findOrFail($mid);

        if ($message->user_id !== $authId) {
            throw new ChatException("You can only update your own message.");
        }

        if ($enable_update_time) {
            if ($message->created_at->diffInMinutes(now()) > $update_time_limit) {
                throw new ChatException("You can only update message within $update_time_limit mins.");
            }
        }

        $message->update([
            'message' => $request->message,
            'is_updated' => true
        ]);
        return $message;
    }

    public function deleteMessage($request, $chat, $mid)
    {
        $authId = auth()->id();
        $message = $chat->messages()->where('type', ChatMessage::MESSAGE)->findOrFail($mid);

        $deleteForEveryone = $request->boolean('delete_for_everyone');

        if ($deleteForEveryone) {
            $enable_delete_time = config('chat.message.enable_delete_time', true);
            $delete_time_limit = config('chat.message.delete_time_limit', 60);

            if (!is_null($message->deleted_at)) {
                throw new ChatException("Message has already been deleted for everyone.");
            }

            // Only sender can delete for everyone
            if ($message->user_id !== $authId) {
                throw new ChatException("You can only delete your own messages for everyone.");
            }

            if ($enable_delete_time) {
                if ($message->created_at->diffInMinutes(now()) > $delete_time_limit) {
                    throw new ChatException("You can only delete messages for everyone within $delete_time_limit mins.");
                }
            }

            $message->update(['deleted_at' => now()]);
        } else {
            // "Delete for me" – store a user-specific deletion
            $message->deletions()->firstOrCreate(['user_id' => $authId]);
        }

        return "Message has been deleted.";
    }
}
