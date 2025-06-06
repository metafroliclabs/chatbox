<?php

namespace Metafroliclabs\LaravelChat\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Metafroliclabs\LaravelChat\Exceptions\ChatException;
use Metafroliclabs\LaravelChat\Models\Chat;
use Metafroliclabs\LaravelChat\Services\Core\BaseService;

class ChatUserService extends BaseService
{
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

        // Check if the current user is allowed to add users
        $setting = $chat->setting;
        $authPivot = $chat->users()->where('user_id', $authId)->first();
        if (!$setting->can_add_users && $authPivot->pivot->role !== Chat::ADMIN) {
            throw new ChatException("Only admins can add users");
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

        if (!empty($userTimestamps)) {
            // Load user names for activity message
            $addedUsers = User::whereIn('id', array_keys($userTimestamps))->get();

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
            throw new ChatException("Only admins can remove users");
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

        if (!empty($removableUserIds)) {
            // Fetch removed user names
            $removedUsers = User::whereIn('id', $removableUserIds)->get();

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
        $chat = Chat::where('type', Chat::GROUP)
            ->whereHas('users', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->findOrFail($id);

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
}
