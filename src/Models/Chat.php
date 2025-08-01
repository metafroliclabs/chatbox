<?php

namespace Metafroliclabs\LaravelChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    public const PRIVATE = "private";
    public const GROUP = "group";

    public const ADMIN = "admin";
    public const USER = "user";

    protected $fillable = [
        'type',
        'name',
        'image',
        'created_by'
    ];

    protected $with = ['users', 'messages', 'setting'];

    // Relations
    public function createdBy()
    {
        $UserModel = config('chat.user.model', \App\Models\User::class);
        return $this->belongsTo($UserModel, 'created_by');
    }

    public function setting()
    {
        return $this->hasOne(ChatSetting::class);
    }

    public function users()
    {
        $UserModel = config('chat.user.model', \App\Models\User::class);
        return $this->belongsToMany($UserModel, 'chat_users')->withPivot('role', 'bg_notification', 'cleared_at', 'created_at');
    }

    // public function users()
    // {
    //     return $this->hasMany(ChatUser::class);
    // }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    // Accessors
    public function getImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    // Scopes
    public function scopeOrderByLatestActivity($query)
    {
        return $query->addSelect([
            'latest_message_at' => ChatMessage::select('created_at')
                ->whereColumn('chat_id', 'chats.id')
                ->where('type', ChatMessage::MESSAGE)
                ->latest('created_at')
                ->limit(1),
        ])
            ->orderByRaw('COALESCE(latest_message_at, chats.created_at) DESC');
    }

    public function scopeWithUser($query, $userId)
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
