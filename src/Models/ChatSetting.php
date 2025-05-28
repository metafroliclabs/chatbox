<?php

namespace Metafroliclabs\LaravelChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'can_add_users',
        'can_send_messages',
        'can_update_settings',
    ];

    // Relations
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
