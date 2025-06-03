<?php

namespace Metafroliclabs\LaravelChat\Services;

use Exception;
use Metafroliclabs\LaravelChat\Models\ChatMessageView;
use Metafroliclabs\LaravelChat\Services\Core\BaseService;
use Metafroliclabs\LaravelChat\Services\Core\FileService;

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

        $query = $chat->messages()->latest();

        $messages = $this->pagination
            ? $query->paginate($this->per_page)
            : $query->get();

        // Get IDs of messages not yet viewed by the current user and not sent by the user
        $unseenMessages = $messages->filter(function ($msg) use ($authId) {
            return $msg->user_id !== $authId && !$msg->views->contains('user_id', $authId);
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

        // Insert unseen view records
        if (!empty($viewData)) {
            ChatMessageView::insert($viewData);
        }

        return $messages;
    }

    public function sendMessage($chat, $request)
    {
        $messages = array();
        if ($request->attachments) {
            $uploaded = $this->fileService->uploadMultipleFiles($request->attachments);
            $images = $uploaded['data'];

            foreach ($images as $key => $image) {
                $message = $chat->messages()->create(['user_id' => auth()->id()]);
                $message->attachment()->create($image);
                $messages[] = $message;
            }
        }

        if ($request->message) {
            $message = $chat->messages()->create([
                'user_id' => auth()->id(),
                'message' => $request->message,
            ]);
            $messages[] = $message;
        }

        return $messages;
    }

    public function getMessageLikes($chat, $mid)
    {
        $message = $chat->messages()->findOrFail($mid);
        return $message->reactions()->get();
    }

    public function toggleLike($chat, $mid)
    {
        $message = $chat->messages()->findOrFail($mid);
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
        $message = $chat->messages()->findOrFail($mid);
        return $message->views()->get();
    }

    public function viewMessage($chat, $mid)
    {
        $message = $chat->messages()->findOrFail($mid);
        $view = $message->views()->create(['user_id' => auth()->id()]);

        return $view;
    }

    public function deleteMessage($request, $chat, $mid)
    {
        $authId = auth()->id();
        $message = $chat->messages()->findOrFail($mid);
    
        $deleteForEveryone = $request->boolean('delete_for_everyone');
    
        if ($deleteForEveryone) {
            // Only sender can delete for everyone, and only within 1 hour
            if ($message->user_id !== $authId) {
                throw new Exception("You can only delete your own messages for everyone.");
            }
    
            if ($message->created_at->diffInMinutes(now()) > 60) {
                throw new Exception("You can only delete messages for everyone within 1 hour.");
            }
    
            $message->update(['deleted_at' => now()]);
        } else {
            // "Delete for me" – store a user-specific deletion
            $message->deletions()->firstOrCreate(['user_id' => $authId]);
        }
    
        return "Message has been deleted.";
    }
}
