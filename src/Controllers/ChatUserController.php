<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Requests\UsersRequest;
use Metafroliclabs\LaravelChat\Resources\DefaultResource;
use Metafroliclabs\LaravelChat\Services\ChatService;

class ChatUserController extends Controller
{
    public $chatService;
    protected $response;

    public function __construct(ChatResponseContract $response, ChatService $chatService)
    {
        $this->response = $response;
        $this->chatService = $chatService;
    }

    public function get_users($id)
    {
        $users = $this->chatService->get_chat_users($id);
        return $this->response->success(DefaultResource::collection($users));
    }

    public function add_users(UsersRequest $request, $id)
    {
        $users = $this->chatService->add_users($request, $id);
        return $this->response->success(DefaultResource::collection($users));
    }

    public function remove_users(UsersRequest $request, $id)
    {
        $users = $this->chatService->remove_users($request, $id);
        return $this->response->success(DefaultResource::collection($users));
    }

    public function manage_admin($id, $uid)
    {
        $users = $this->chatService->manage_admin($id, $uid);
        return $this->response->success(DefaultResource::collection($users));
    }
}
