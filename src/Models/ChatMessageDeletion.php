<?php

namespace Metafroliclabs\LaravelChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Metafroliclabs\LaravelChat\Traits\HasUser;

class ChatMessageDeletion extends Model
{
    use HasFactory, HasUser;

    protected $fillable = [
        'chat_message_id',
        'user_id',
    ];

    // Relations
    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }
}
