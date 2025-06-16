<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Requests\ForwardMessageRequest;
use Metafroliclabs\LaravelChat\Requests\MessageRequest;
use Metafroliclabs\LaravelChat\Resources\DefaultResource;
use Metafroliclabs\LaravelChat\Resources\MessageResource;
use Metafroliclabs\LaravelChat\Services\ChatMessageService;
use Metafroliclabs\LaravelChat\Services\ChatService;

class ChatMessageController extends Controller
{
    public $chatService;
    public $messageService;
    protected $response;
    protected $pagination;

    public function __construct(ChatResponseContract $response, ChatMessageService $messageService, ChatService $chatService)
    {
        $this->response = $response;
        $this->chatService = $chatService;
        $this->messageService = $messageService;
        $this->pagination = config('chat.pagination', true);
    }

    public function index($id)
    {
        $chat = $this->chatService->get_chat($id);
        $messages = $this->messageService->getChatMessages($chat);

        $resource = MessageResource::collection($messages);

        $result = $this->pagination
            ? $resource->response()->getData(true)
            : $resource;

        return $this->response->success($result);
    }

    public function send_message(MessageRequest $request, $id)
    {
        $chat = $this->chatService->get_chat($id);
        $messages = $this->messageService->sendMessage($chat, $request);
        return $this->response->success(MessageResource::collection($messages));
    }

    public function forward_messages(ForwardMessageRequest $request, $id)
    {
        $chat = $this->chatService->get_chat($id);
        $messages = $this->messageService->forwardMessages($chat, $request);
        return $this->response->success(['message' => "Messages have been forwarded"]);
    }

    public function get_message_likes($id, $mid)
    {
        $chat = $this->chatService->get_chat($id);
        $likes = $this->messageService->getMessageLikes($chat, $mid);
        return $this->response->success(DefaultResource::collection($likes));
    }

    public function like_message($id, $mid)
    {
        $chat = $this->chatService->get_chat($id);
        $message = $this->messageService->toggleLike($chat, $mid);
        return $this->response->success(['message' => $message]);
    }

    public function get_message_views($id, $mid)
    {
        $chat = $this->chatService->get_chat($id);
        $views = $this->messageService->getMessageViews($chat, $mid);
        return $this->response->success(DefaultResource::collection($views));
    }

    public function view_message($id, $mid)
    {
        $chat = $this->chatService->get_chat($id);
        $view = $this->messageService->viewMessage($chat, $mid);
        return $this->response->success(new DefaultResource($view));
    }

    public function update_message(Request $request, $id, $mid)
    {
        $request->validate(['message' => 'required']);

        $chat = $this->chatService->get_chat($id);
        $message = $this->messageService->updateMessage($request, $chat, $mid);
        return $this->response->success(new MessageResource($message));
    }
    
    public function delete_message(Request $request, $id, $mid)
    {
        $request->validate(['delete_for_everyone' => 'nullable|in:0,1']);

        $chat = $this->chatService->get_chat($id);
        $message = $this->messageService->deleteMessage($request, $chat, $mid);
        return $this->response->success(['message' => $message]);
    }
}
