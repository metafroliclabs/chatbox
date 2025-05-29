<?php

namespace Metafroliclabs\LaravelChat\Services;

use Metafroliclabs\LaravelChat\Services\Core\BaseService;
use Metafroliclabs\LaravelChat\Services\Core\FileService;

class ChatMessageService extends BaseService
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
    
    public function getChatMessages($chat)
    {
        $messages = $chat->messages()->latest()->paginate();
        return $messages;
    }

    public function sendMessage($chat, $request)
    {
        $messages = array();
        if ($request->attachments) {
            $uploaded = $this->fileService->uploadMultipleFiles($request->attachments, "Img", "attachments");
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
}