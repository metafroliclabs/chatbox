<?php

namespace Metafroliclabs\LaravelChat\Services;

use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Exceptions\ChatException;
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

    public function get_chat($id)
    {
        $chat = Chat::with('messages.repliedTo')
            ->withUser(auth()->id())->findOrFail($id);

        return $chat;
    }

    public function get_chat_list($request)
    {
        $userId = auth()->id();
        $searchTerm = '%' . $request->search . '%';

        $query = Chat::withUser($userId);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Get configured user filters + search
        $filters = config('chat.user.filters', []);
        $filters[] = 'search';

        

        // Determine if any user filter other than 'search' is filled
        $userFilters = array_filter($filters, fn($f) => $f !== 'search');

        $onlySearchFilled = $request->filled('search') &&
            !collect($userFilters)->some(fn($f) => $request->filled($f));

        // Apply user-based filtering + search
        if (collect($filters)->some(fn($f) => $request->filled($f))) {
            $query->where(function ($q) use ($request, $filters, $searchTerm, $userId, $onlySearchFilled) {
                $q->where(function ($q2) use ($request, $filters, $searchTerm, $userId) {
                    $q2->where('type', Chat::PRIVATE)
                        ->whereHas('users', function ($q3) use ($request, $filters, $searchTerm, $userId) {
                            $q3->where('user_id', '!=', $userId);

                            foreach ($filters as $filter) {
                                if (!$request->filled($filter)) {
                                    continue;
                                }

                                if ($filter === 'search') {
                                    $q3->where($this->getNameColumn(), 'like', $searchTerm);
                                } else {
                                    $q3->where($filter, $request->$filter);
                                }
                            }
                        });
                });

                // Allow group chat name search ONLY if no user filters are present
                if ($onlySearchFilled) {
                    $q->orWhere('name', 'like', $searchTerm);
                }
            });
        }

        // Order by latest message
        $query->orderByLatestActivity();

        return $this->pagination
            ? $query->paginate($this->per_page)
            : $query->get();
    }

    public function get_unread_chat_list($request)
    {
        $userId = auth()->id();
        $searchTerm = '%' . $request->search . '%';

        $query = Chat::withUser($userId)
            ->whereHas('messages', function ($q) use ($userId) {
                $q->where('user_id', '!=', $userId)
                    ->where('type', ChatMessage::MESSAGE)
                    ->whereDoesntHave('views', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    })
                    ->where(function ($q3) use ($userId) {
                        $q3->whereHas('chat.users', function ($q4) use ($userId) {
                            $q4->where('user_id', $userId)
                                ->where(function ($q5) {
                                    $q5->whereColumn('chat_messages.created_at', '>=', 'chat_users.created_at')
                                        ->orWhereNull('chat_users.created_at');
                                })
                                ->where(function ($q6) {
                                    $q6->whereColumn('chat_messages.created_at', '>=', 'chat_users.cleared_at')
                                        ->orWhereNull('chat_users.cleared_at');
                                });
                        });
                    });
            });

        // Apply type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Get configured user filters + search
        $filters = config('chat.user.filters', []);
        $filters[] = 'search';

        // Determine if any user filter other than 'search' is filled
        $userFilters = array_filter($filters, fn($f) => $f !== 'search');

        $onlySearchFilled = $request->filled('search') &&
            !collect($userFilters)->some(fn($f) => $request->filled($f));

        // Apply user-based filtering + search
        if (collect($filters)->some(fn($f) => $request->filled($f))) {
            $query->where(function ($q) use ($request, $filters, $searchTerm, $userId, $onlySearchFilled) {
                $q->where(function ($q2) use ($request, $filters, $searchTerm, $userId) {
                    $q2->where('type', Chat::PRIVATE)
                        ->whereHas('users', function ($q3) use ($request, $filters, $searchTerm, $userId) {
                            $q3->where('user_id', '!=', $userId);

                            foreach ($filters as $filter) {
                                if (!$request->filled($filter)) {
                                    continue;
                                }

                                if ($filter === 'search') {
                                    $q3->where($this->getNameColumn(), 'like', $searchTerm);
                                } else {
                                    $q3->where($filter, $request->$filter);
                                }
                            }
                        });
                });

                // Allow group chat name search ONLY if no user filters are present
                if ($onlySearchFilled) {
                    $q->orWhere('name', 'like', $searchTerm);
                }
            });
        }

        // Order by latest_message if exists, otherwise chats.created_at
        $query->orderByLatestActivity();

        return $this->pagination
            ? $query->paginate($this->per_page)
            : $query->get();
    }

    public function get_unread_chat_count()
    {
        $userId = auth()->id();

        $counts = Chat::withUser($userId)
            ->whereHas('messages', function ($q) use ($userId) {
                $q->where('user_id', '!=', $userId)
                    ->where('type', ChatMessage::MESSAGE)
                    ->whereDoesntHave('views', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    })
                    ->where(function ($q3) use ($userId) {
                        $q3->whereHas('chat.users', function ($q4) use ($userId) {
                            $q4->where('user_id', $userId)
                                ->where(function ($q5) {
                                    $q5->whereColumn('chat_messages.created_at', '>=', 'chat_users.created_at')
                                        ->orWhereNull('chat_users.created_at');
                                })
                                ->where(function ($q6) {
                                    $q6->whereColumn('chat_messages.created_at', '>=', 'chat_users.cleared_at')
                                        ->orWhereNull('chat_users.cleared_at');
                                });
                        });
                    });
            })
            ->count();

        return $counts;
    }

    public function create_chat($userId)
    {
        $authId = auth()->id();

        if ($authId == $userId) {
            throw new ChatException("Trying to create a chat with invalid user id.");
        }

        $chat = Chat::withUser($authId)->withUser($userId)
            ->where('type', Chat::PRIVATE)->first();

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

    public function create_chat_group($data)
    {
        $authId = auth()->id();
        $image = null;

        if (isset($data['picture'])) {
            $image = $this->fileService->uploadFile($data['picture'], 'File', 'chat');
        }

        DB::beginTransaction();
        $chat = Chat::create([
            'type' => Chat::GROUP,
            'name' => $data['name'],
            'image' => $image ? $image['data'] : $image,
            'created_by' => $authId
        ]);

        // create chat setting record
        $chat->setting()->create();

        // create group creation activity
        $message = $this->getFullName(auth()->user()) . ' created the group "' . $data['name'] . '"';
        $this->logActivity($chat, $message);

        // Attach creator as admin
        $chat->users()->attach($authId, [
            'role' => Chat::ADMIN,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Attach other users
        $userTimestamps = [];
        foreach ($data['users'] as $userId) {
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

        $chat = Chat::withUser($userId)->where('type', Chat::GROUP)->findOrFail($id);

        // Check if user is admin
        $pivotData = $chat->users()->where('user_id', $userId)->first();
        $isAdmin = $pivotData?->pivot?->role === Chat::ADMIN;

        DB::beginTransaction();
        // Detach user
        $chat->users()->detach($userId);

        // create group creation activity
        $message = $this->getFullName(auth()->user()) . ' left';
        $this->logActivity($chat, $message);

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
        DB::commit();

        return $chat;
    }

    public function update_chat($request, $id)
    {
        $authId = auth()->id();
        $actorName = $this->getFullName(auth()->user());
        $image = null;
        $activityMessages = [];

        $chat = Chat::withUser($authId)->where('type', Chat::GROUP)->findOrFail($id);

        $setting = $chat->setting;
        $authPivot = $chat->users()->where('user_id', $authId)->first();

        if (!$setting->can_update_settings && $authPivot->pivot->role !== Chat::ADMIN) {
            throw new ChatException("Only admins can update group's settings.");
        }

        DB::beginTransaction();
        // Track name change
        if ($request->filled('name') && $request->name !== $chat->name) {
            $activityMessages[] = $actorName . ' changed this group\'s name to "' . $request->name . '"';
        }

        if ($request->hasFile('picture')) {
            $image = $this->fileService->uploadFile($request->picture, 'File', 'chat');
            $activityMessages[] = "{$actorName} updated this group's image";
        }

        $chat->update([
            'name' => $request->name,
            'image' => $image ? $image['data'] : $image,
        ]);

        // Group setting update (admin only)
        if ($authPivot->pivot->role === Chat::ADMIN) {
            $settingData = [];
            $changes = [];

            if ($request->filled('can_add_users') && $request->can_add_users != $setting->can_add_users) {
                $settingData['can_add_users'] = $request->can_add_users;
                $changes[] = 'user management permission';
            }

            if ($request->filled('can_send_messages') && $request->can_send_messages != $setting->can_send_messages) {
                $settingData['can_send_messages'] = $request->can_send_messages;
                $changes[] = 'sending message permission';
            }

            if ($request->filled('can_update_settings') && $request->can_update_settings != $setting->can_update_settings) {
                $settingData['can_update_settings'] = $request->can_update_settings;
                $changes[] = 'settings permission';
            }

            if (!empty($settingData)) {
                $setting->fill(array_merge(['chat_id' => $chat->id], $settingData))->save();

                // Grouped activity message
                $activityMessages[] = "$actorName updated the group's " . implode(', ', $changes) . '.';
            }
        }
        $this->logActivity($chat, $activityMessages);
        DB::commit();

        $chat->load('setting');
        return $chat;
    }

    public function clear_chat($id)
    {
        $userId = auth()->id();

        $chat = Chat::withUser($userId)->findOrFail($id);

        $chat->users()->updateExistingPivot($userId, [
            'cleared_at' => now()
        ]);

        return $chat;
    }

    public function mute_chat($id)
    {
        $userId = auth()->id();

        $chat = Chat::withUser($userId)->findOrFail($id);

        $authPivot = $chat->users()->where('user_id', $userId)->first();
        $notification = $authPivot->pivot->bg_notification;

        $chat->users()->updateExistingPivot($userId, [
            'bg_notification' => !$notification
        ]);

        $chat->load('users');
        return $chat;
    }
}
