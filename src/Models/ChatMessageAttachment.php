<?php

namespace Metafroliclabs\LaravelChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_message_id',
        'file',
        'type',
    ];

    // Relations
    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }
}
