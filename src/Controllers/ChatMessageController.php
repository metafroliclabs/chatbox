<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Requests\MessageRequest;
use Metafroliclabs\LaravelChat\Resources\MessageResource;
use Metafroliclabs\LaravelChat\Services\ChatMessageService;
use Metafroliclabs\LaravelChat\Services\ChatService;

class ChatMessageController extends Controller
{
    public $chatService;
    public $messageService;
    protected $response;

    public function __construct(ChatResponseContract $response, ChatMessageService $messageService, ChatService $chatService)
    {
        $this->chatService = $chatService;
        $this->messageService = $messageService;
        $this->response = $response;
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
