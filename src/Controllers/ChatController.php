<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Resources\ChatResource;
use Metafroliclabs\LaravelChat\Requests\CreateGroupRequest;
use Metafroliclabs\LaravelChat\Services\ChatService;

class ChatController extends Controller
{
    public $chatService;
    protected $response;

    public function __construct(ChatResponseContract $response, ChatService $chatService)
    {
        $this->chatService = $chatService;
        $this->response = $response;
    }

    public function index(Request $request)
    {
        $chats = $this->chatService->get_chat_list($request);
        return $this->response->success(ChatResource::collection($chats));
    }

    public function unread_list(Request $request)
    {
        $chats = $this->chatService->get_unread_chat_list($request);
        return $this->response->success(ChatResource::collection($chats));
    }

    public function unread_count()
    {
        $count = $this->chatService->get_unread_chat_count();
        return $this->response->success(["count" => $count]);
    }
    
    public function create(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $chat = $this->chatService->create_chat($request);
        return $this->response->success(new ChatResource($chat));
    }

    public function create_group(CreateGroupRequest $request)
    {
        $chat = $this->chatService->create_chat_group($request);
        return $this->response->success(new ChatResource($chat));
    }

    public function leave($id)
    {
        $chat = $this->chatService->leave_chat_group($id);
        return $this->response->success(["message" => "Group left successfully"]);
    }
}
