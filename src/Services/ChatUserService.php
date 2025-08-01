<?php

namespace Metafroliclabs\LaravelChat\Services;

use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Exceptions\ChatException;
use Metafroliclabs\LaravelChat\Models\Chat;
use Metafroliclabs\LaravelChat\Services\Core\BaseService;

class ChatUserService extends BaseService
{
    public function get_chat_users($id)
    {
        $chat = Chat::withUser(auth()->id())->findOrFail($id);

        return $chat->users;
    }

    public function add_users($userIds, $id)
    {
        $authId = auth()->id();
        $maxUsers = config('chat.group.max_users', 9);

        // Fetch the chat and ensure the user is part of it
        $chat = Chat::withUser($authId)->where('type', Chat::GROUP)->findOrFail($id);

        // Check if the current user is allowed to add users
        $setting = $chat->setting;
        $authPivot = $chat->users()->where('user_id', $authId)->first();
        if (!$setting->can_add_users && $authPivot->pivot->role !== Chat::ADMIN) {
            throw new ChatException("Only admins can add users");
        }

        // Prepare users to attach
        $existingUserIds = $chat->users()->pluck('user_id')->toArray();
        $userTimestamps = [];

        foreach ($userIds as $userId) {
            if ($userId == $authId || in_array($userId, $existingUserIds)) {
                continue; // Skip if self or already in chat
            }

            $userTimestamps[$userId] = [
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Max users check
        if ((count($existingUserIds) + count($userTimestamps)) > $maxUsers) {
            throw new ChatException("Max {$maxUsers} users are allowed in a group chat.");
        }

        if (!empty($userTimestamps)) {
            // Load user names for activity message
            $UserModel = config('chat.user.model', \App\Models\User::class);
            $addedUsers = $UserModel::whereIn('id', array_keys($userTimestamps))->get();

            $addedNames = $addedUsers->map(function ($user) {
                return $this->getFullName($user);
            })->toArray();

            $actorName = $this->getFullName(auth()->user());
            $namesString = implode(', ', $addedNames);

            $message = $actorName . ' added ' . $namesString . ' to the group';

            DB::beginTransaction();
            // Attach only new users
            $chat->users()->attach($userTimestamps);

            // Create activity message
            $this->logActivity($chat, $message);
            DB::commit();
        }

        $chat->load('users');
        return $chat->users;
    }

    public function remove_users($userIds, $id)
    {
        $authId = auth()->id();

        // Find the chat and check if the user is part of it
        $chat = Chat::withUser($authId)->where('type', Chat::GROUP)->findOrFail($id);

        // Check if the authenticated user is an admin
        $authPivot = $chat->users()->where('user_id', $authId)->first();
        if (!$authPivot || $authPivot->pivot->role !== Chat::ADMIN) {
            throw new ChatException("Only admins can remove users");
        }

        // Get valid members in the chat
        $existingUserIds = $chat->users()->pluck('user_id')->toArray();

        // Determine users to remove (exclude self, and non-members)
        $removableUserIds = array_filter($userIds, function ($userId) use ($authId, $existingUserIds) {
            return $userId != $authId && in_array($userId, $existingUserIds);
        });

        if (!empty($removableUserIds)) {
            // Fetch removed user names
            $UserModel = config('chat.user.model', \App\Models\User::class);
            $removedUsers = $UserModel::whereIn('id', $removableUserIds)->get();

            $removedNames = $removedUsers->map(fn($user) => $this->getFullName($user))->toArray();
            $actorName = $this->getFullName(auth()->user());
            $namesString = implode(', ', $removedNames);
            $message = "$actorName removed $namesString from the group";

            DB::beginTransaction();
            // Detach the selected users
            $chat->users()->detach($removableUserIds);

            // Create activity message
            $this->logActivity($chat, $message);
            DB::commit();
        }

        $chat->load('users');
        return $chat->users;
    }

    public function manage_admin($id, $uid)
    {
        $authId = auth()->id();

        // Find the chat and check if the user is part of it
        $chat = Chat::withUser($authId)->where('type', Chat::GROUP)->findOrFail($id);

        // Ensure the authenticated user is an admin
        $authPivot = $chat->users()->where('user_id', $authId)->first();
        if (!$authPivot || $authPivot->pivot->role !== Chat::ADMIN) {
            throw new ChatException("Only admins can perform this action.");
        }

        // Prevent modifying own admin role (optional)
        if ($uid == $authId) {
            throw new ChatException("You cannot modify your own admin role.");
        }

        // Find target user in the chat
        $user = $chat->users()->where('user_id', $uid)->first();
        if (!$user) {
            throw new ChatException("User is not in the chat.");
        }

        // Toggle role
        $newRole = $user->pivot->role === Chat::ADMIN ? Chat::USER : Chat::ADMIN;
        $chat->users()->updateExistingPivot($uid, ['role' => $newRole]);

        $chat->load('users');
        return $chat->users;
    }

    // For universal chat only
    public function addUserToUniversalChat($userId)
    {
        $chat = Chat::where('type', Chat::GROUP)->first();

        if (!$chat) {
            $newChat = Chat::create([
                'type' => Chat::GROUP,
                'name' => 'World Chat',
            ]);

            $newChat->setting()->create(['can_add_users' => false, 'can_update_settings' => false]);

            $newChat->users()->attach($userId, ['created_at' => now(), 'updated_at' => now()]);

            return $newChat;
        }

        $user = $chat->users()->where('user_id', $userId)->first();
        if (!$user) {
            $chat->users()->attach($userId, ['created_at' => now(), 'updated_at' => now()]);
        }

        $chat->load('users');
        return $chat;
    }
}
