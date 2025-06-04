<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Resources\ChatResource;
use Metafroliclabs\LaravelChat\Requests\CreateGroupRequest;
use Metafroliclabs\LaravelChat\Requests\UpdateGroupRequest;
use Metafroliclabs\LaravelChat\Services\ChatService;

class ChatController extends Controller
{
    public $chatService;
    protected $response;
    protected $pagination;

    public function __construct(ChatResponseContract $response, ChatService $chatService)
    {
        $this->response = $response;
        $this->chatService = $chatService;
        $this->pagination = config('chat.pagination', true);
    }

    public function index(Request $request)
    {
        $chats = $this->chatService->get_chat_list($request);

        $resource = ChatResource::collection($chats);

        $result = $this->pagination
            ? $resource->response()->getData(true)
            : $resource;

        return $this->response->success($result);
    }

    public function unread_list(Request $request)
    {
        $chats = $this->chatService->get_unread_chat_list($request);

        $resource = ChatResource::collection($chats);

        $result = $this->pagination
            ? $resource->response()->getData(true)
            : $resource;

        return $this->response->success($result);
    }

    public function unread_count()
    {
        $count = $this->chatService->get_unread_chat_count();
        return $this->response->success(["count" => $count]);
    }

    public function create(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $chat = $this->chatService->create_chat($request->user_id);
        return $this->response->success(new ChatResource($chat));
    }

    public function create_group(CreateGroupRequest $request)
    {
        $chat = $this->chatService->create_chat_group($request);
        return $this->response->success(new ChatResource($chat));
    }

    public function update(UpdateGroupRequest $request, $id)
    {
        $chat = $this->chatService->update_chat($request, $id);
        return $this->response->success(new ChatResource($chat));
    }

    public function leave($id)
    {
        $chat = $this->chatService->leave_chat_group($id);
        return $this->response->success(["message" => "Group left successfully"]);
    }

    public function delete($id)
    {
        $chat = $this->chatService->clear_chat($id);
        return $this->response->success(["message" => "Chat has been deleted successfully"]);
    }

    public function mute($id)
    {
        $chat = $this->chatService->mute_chat($id);
        return $this->response->success(new ChatResource($chat));
    }
}
