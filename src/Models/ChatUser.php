<?php

namespace Metafroliclabs\LaravelChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Metafroliclabs\LaravelChat\Traits\HasUser;

class ChatUser extends Model
{
    use HasFactory, HasUser;

    protected $fillable = [
        'chat_id',
        'user_id',
        'role',
        'bg_notification',
        'cleared_at',
    ];

    // Relations
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
