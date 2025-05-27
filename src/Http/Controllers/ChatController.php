<?php

namespace Metafroliclabs\LaravelChat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Http\Requests\MessageRequest;
use Metafroliclabs\LaravelChat\Http\Resources\ChatResource;
use Metafroliclabs\LaravelChat\Http\Resources\MessageResource;
use Metafroliclabs\LaravelChat\Models\Chat;

class ChatController extends Controller
{
    public function create_chat(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $chat = Chat::with('users')
            ->whereHas('users', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereHas('users', function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->first();

        if ($chat)
            return response()->json(new ChatResource($chat));

        DB::beginTransaction();
        $newChat = Chat::create(['type' => Chat::PRIVATE]);

        $newChat->users()->attach(auth()->id(), [
            'is_admin'   => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $newChat->users()->attach($request->user_id, [
            'is_admin'   => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::commit();
        return response()->json(new ChatResource($newChat));
    }

    public function get_unread_count()
    {
        $count = Chat::with('users', 'messages')
            ->whereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereHas('messages', function ($q) {
                $q->whereNull('read_at')->where('user_id', '!=', auth()->id());
            })
            ->count();
        return response()->json(['count' => $count]);
    }

    public function get_unread_chats(Request $request)
    {
        $chats = Chat::with('users', 'messages')
            ->whereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereHas('messages', function ($q) {
                $q->whereNull('read_at')->where('user_id', '!=', auth()->id());
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where(function ($subquery) use ($request) {
                        $subquery->where('type', Chat::PRIVATE)
                            ->whereHas('users', function ($subquery2) use ($request) {
                                $subquery2->where(DB::raw('concat(first_name," ",last_name)'), 'like', '%' . $request->search . '%');
                            });
                    })
                        ->orWhere('name', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->orderByDesc(function ($q) {
                $q->select('created_at')
                    ->from('chat_messages')
                    ->whereColumn('chat_id', 'chats.id')
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
            })
            ->get();

        return response()->json(ChatResource::collection($chats));
    }

    public function get_chat_list(Request $request)
    {
        $chats = Chat::with('users', 'messages')
            ->whereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where(function ($subquery) use ($request) {
                        $subquery->where('type', Chat::PRIVATE)
                            ->whereHas('users', function ($subquery2) use ($request) {
                                $subquery2->where(DB::raw('concat(first_name," ",last_name)'), 'like', '%' . $request->search . '%');
                            });
                    })
                        ->orWhere('name', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->orderByDesc(function ($q) {
                $q->select('created_at')
                    ->from('chat_messages')
                    ->whereColumn('chat_id', 'chats.id')
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
            })
            ->get();

        return response()->json(ChatResource::collection($chats));
    }

    public function get_chat($id)
    {
        $chat = Chat::with('users', 'messages')
            ->whereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        $chat->messages()->whereNull('read_at')->where('user_id', '!=', auth()->id())->update(['read_at' => now()]);

        $messages = $chat->messages()->latest()->get();
        return response()->json(MessageResource::collection($messages));
    }

    public function send_message(MessageRequest $request, $id)
    {
        $chat = Chat::with('users')->whereHas('users', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        $chat->messages()->whereNull('read_at')->where('user_id', '!=', auth()->id())->update(['read_at' => now()]);

        // DB::beginTransaction();
        $message = $chat->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message ?? null,
        ]);
        // DB::commit();

        return response()->json(new MessageResource($message));
    }
}
