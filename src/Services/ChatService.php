<?php

namespace Metafroliclabs\LaravelChat\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Models\Chat;
use Metafroliclabs\LaravelChat\Models\ChatMessage;
use Metafroliclabs\LaravelChat\Services\Core\BaseService;
use Metafroliclabs\LaravelChat\Services\Core\FileService;

class ChatService extends BaseService
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        parent::__construct();
        $this->fileService = $fileService;
    }

    // private function getNameColumn()
    // {
    //     $cols = config('chat.name_cols_in_users_table');
    //     if (count($cols) > 1) {
    //         $str = implode(", ' ', ", $cols);
    //         return DB::raw("CONCAT($str)");
    //     }
    //     return $cols[0];
    // }

    public function get_chat($id)
    {
        $chat = Chat::with('messages.repliedTo')
            ->whereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        return $chat;
    }

    public function get_chat_list($request)
    {
        $userId = auth()->id();
        $searchTerm = '%' . $request->search . '%';

        $query = Chat::whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        // Filter by search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where(function ($q2) use ($searchTerm) {
                    $q2->where('type', Chat::PRIVATE)
                        ->whereHas('users', function ($q3) use ($searchTerm) {
                            $q3->where('first_name', 'like', $searchTerm);
                        });
                })->orWhere('name', 'like', $searchTerm);
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Order by latest message
        $query->orderByDesc(
            ChatMessage::select('created_at')
                ->whereColumn('chat_id', 'chats.id')
                ->orderByDesc('created_at')
                ->limit(1)
        );

        return $this->pagination ? $query->paginate($this->per_page) : $query->get();
    }

    public function get_unread_chat_list($request)
    {
        $userId = auth()->id();
        $searchTerm = '%' . $request->search . '%';

        $query = Chat::whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->whereHas('messages', function ($q) use ($userId) {
                $q->where('user_id', '!=', $userId)
                    ->whereDoesntHave('views', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    });
            });

        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where(function ($q2) use ($searchTerm) {
                    $q2->where('type', Chat::PRIVATE)
                        ->whereHas('users', function ($q3) use ($searchTerm) {
                            $q3->where('name', 'like', $searchTerm);
                        });
                })->orWhere('name', 'like', $searchTerm);
            });
        }

        // Apply type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Order by latest message timestamp
        $query->orderByDesc(
            ChatMessage::select('created_at')
                ->whereColumn('chat_id', 'chats.id')
                ->orderByDesc('created_at')
                ->limit(1)
        );

        return $this->pagination ? $query->paginate($this->per_page) : $query->get();
    }

    public function get_unread_chat_count()
    {
        $userId = auth()->id();

        $counts = Chat::whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->whereHas('messages', function ($q) use ($userId) {
                $q->where('user_id', '!=', $userId)
                    ->whereDoesntHave('views', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    });
            })
            ->count();

        return $counts;
    }

    public function create_chat($request)
    {
        $authId = auth()->id();
        $userId = $request->user_id;

        if ($authId == $userId) {
            throw new Exception("Trying to create a chat with invalid user id.");
        }

        $chat = Chat::whereHas('users', function ($query) use ($authId) {
            $query->where('user_id', $authId);
        })
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('type', Chat::PRIVATE)
            ->first();

        if ($chat) return $chat;

        DB::beginTransaction();
        $newChat = Chat::create(['type' => Chat::PRIVATE]);

        $newChat->users()->attach([
            $authId => ['created_at' => now(), 'updated_at' => now()],
            $userId => ['created_at' => now(), 'updated_at' => now()]
        ]);
        DB::commit();

        return $newChat;
    }

    public function create_chat_group($request)
    {
        $authId = auth()->id();
        $image = null;

        if ($request->hasFile('picture')) {
            $image = $this->fileService->uploadFile($request->picture, 'Img', 'chat');
        }

        DB::beginTransaction();
        $chat = Chat::create([
            'type' => Chat::GROUP,
            'name' => $request->name,
            'image' => $image ? $image['data'] : $image,
            'created_by' => $authId
        ]);

        // Attach creator as admin
        $chat->users()->attach($authId, [
            'role' => Chat::ADMIN,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Attach other users
        $userTimestamps = [];
        foreach ($request->users as $userId) {
            if ($userId == $authId) continue; // Skip if already added

            $userTimestamps[$userId] = [
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $chat->users()->attach($userTimestamps);
        DB::commit();

        return $chat;
    }

    public function leave_chat_group($id)
    {
        $userId = auth()->id();

        $chat = Chat::where('type', Chat::GROUP)
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->findOrFail($id);

        // Check if user is admin
        $pivotData = $chat->users()->where('user_id', $userId)->first();
        $isAdmin = $pivotData?->pivot?->role === Chat::ADMIN;

        // Detach user
        $chat->users()->detach($userId);

        // Delete chat if no users remain
        if ($chat->users()->count() === 0) {
            $chat->delete();
        } elseif ($isAdmin) {
            // Check if there's already another admin
            $existingAdmin = $chat->users()->wherePivot('role', Chat::ADMIN)->exists();

            // Reassign admin role if needed
            if (!$existingAdmin) {
                $newAdmin = $chat->users()->first();
                if ($newAdmin) {
                    $chat->users()->updateExistingPivot($newAdmin->id, ['role' => Chat::ADMIN]);
                }
            }
        }

        return $chat;
    }

    public function clear_chat($id)
    {
        $userId = auth()->id();

        $chat = Chat::where('type', Chat::GROUP)
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->findOrFail($id);


        $chat->users()->updateExistingPivot($userId, [
            'cleared_at' => now()
        ]);

        return $chat;
    }

    public function get_chat_users($id)
    {
        $chat = Chat::whereHas('users', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->findOrFail($id);

        return $chat->users;
    }

    public function add_users($request, $id)
    {
        $authId = auth()->id();

        // Fetch the chat and ensure the user is part of it
        $chat = Chat::where('type', Chat::GROUP)
            ->whereHas('users', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->findOrFail($id);

        // Check if the current user is admin
        $authPivot = $chat->users()->where('user_id', $authId)->first();
        if (!$authPivot || $authPivot->pivot->role !== Chat::ADMIN) {
            throw new Exception("Only admins can add users");
        }

        // Prepare users to attach
        $existingUserIds = $chat->users()->pluck('user_id')->toArray();
        $userTimestamps = [];

        foreach ($request->users as $userId) {
            if ($userId == $authId || in_array($userId, $existingUserIds)) {
                continue; // Skip if self or already in chat
            }

            $userTimestamps[$userId] = [
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Attach only new users
        if (!empty($userTimestamps)) {
            $chat->users()->attach($userTimestamps);
        }

        $chat->load('users');
        return $chat->users;
    }

    public function remove_users($request, $id)
    {
        $authId = auth()->id();

        // Find the chat and check if the user is part of it
        $chat = Chat::where('type', Chat::GROUP)
            ->whereHas('users', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->findOrFail($id);

        // Check if the authenticated user is an admin
        $authPivot = $chat->users()->where('user_id', $authId)->first();
        if (!$authPivot || $authPivot->pivot->role !== Chat::ADMIN) {
            throw new Exception("Only admins can add users");
        }

        // Get valid members in the chat
        $existingUserIds = $chat->users()->pluck('user_id')->toArray();

        // Determine users to remove (exclude self, and non-members)
        $removableUserIds = array_filter($request->users, function ($userId) use ($authId, $existingUserIds) {
            return $userId != $authId && in_array($userId, $existingUserIds);
        });

        // Detach the selected users
        if (!empty($removableUserIds)) {
            $chat->users()->detach($removableUserIds);
        }

        $chat->load('users');
        return $chat->users;
    }
}
