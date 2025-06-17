<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Requests\UsersRequest;
use Metafroliclabs\LaravelChat\Resources\DefaultResource;
use Metafroliclabs\LaravelChat\Services\ChatUserService;

class ChatUserController extends Controller
{
    public $chatUserService;
    protected $response;

    public function __construct(ChatResponseContract $response, ChatUserService $chatUserService)
    {
        $this->response = $response;
        $this->chatUserService = $chatUserService;
    }

    public function get_users($id)
    {
        $users = $this->chatUserService->get_chat_users($id);
        return $this->response->success(DefaultResource::collection($users));
    }

    public function add_users(UsersRequest $request, $id)
    {
        $users = $this->chatUserService->add_users($request->users, $id);
        return $this->response->success(DefaultResource::collection($users));
    }

    public function remove_users(UsersRequest $request, $id)
    {
        $users = $this->chatUserService->remove_users($request->users, $id);
        return $this->response->success(DefaultResource::collection($users));
    }

    public function manage_admin($id, $uid)
    {
        $users = $this->chatUserService->manage_admin($id, $uid);
        return $this->response->success(DefaultResource::collection($users));
    }
}
