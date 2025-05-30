<?php

namespace Metafroliclabs\LaravelChat\Services;

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
        $query = $chat->messages()->latest();
    
        return $this->pagination
            ? $query->paginate($this->per_page)
            : $query->get();
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
}