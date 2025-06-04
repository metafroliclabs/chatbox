<?php

namespace Metafroliclabs\LaravelChat\Models;

use App\Models\User;
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
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setting()
    {
        return $this->hasOne(ChatSetting::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_users')->withPivot('role', 'bg_notification', 'cleared_at', 'created_at');
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
}