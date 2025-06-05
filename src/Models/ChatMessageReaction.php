<?php

namespace Metafroliclabs\LaravelChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Metafroliclabs\LaravelChat\Traits\HasUser;

class ChatMessageReaction extends Model
{
    use HasFactory, HasUser;

    protected $fillable = [
        'chat_message_id',
        'user_id',
        'reaction_type',
    ];

    protected $with = ['user'];

    // Relations
    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }
}
