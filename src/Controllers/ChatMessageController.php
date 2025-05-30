<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Requests\MessageRequest;
use Metafroliclabs\LaravelChat\Resources\MessageResource;
use Metafroliclabs\LaravelChat\Services\ChatMessageService;
use Metafroliclabs\LaravelChat\Services\ChatService;

class ChatMessageController extends BaseController
{
    public $chatService;
    public $messageService;

    public function __construct(ChatMessageService $messageService, ChatService $chatService)
    {
        parent::__construct();
        $this->chatService = $chatService;
        $this->messageService = $messageService;
    }

    public function index($id)
    {
        $chat = $this->chatService->get_chat($id);
        $messages = $this->messageService->getChatMessages($chat);
        return $this->response->success(MessageResource::collection($messages));
    }

    public function send_message(MessageRequest $request, $id)
    {
        $chat = $this->chatService->get_chat($id);
        $messages = $this->messageService->sendMessage($chat, $request);
        return $this->response->success(MessageResource::collection($messages));
    }
}
