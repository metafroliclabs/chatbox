<?php

namespace Metafroliclabs\LaravelChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafroliclabs\LaravelChat\Traits\HasUser;

class ChatMessage extends Model
{
    use HasFactory, HasUser;

    public const MESSAGE = "message";
    public const ACTIVITY = "activity";

    protected $fillable = [
        'type',
        'chat_id',
        'user_id',
        'message',
        'replied_to_message_id',
        'is_updated',
        'deleted_at',
    ];

    protected $with = ['user', 'attachment', 'reactions', 'views'];

    // Relations
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function repliedTo()
    {
        return $this->belongsTo(ChatMessage::class, 'replied_to_message_id');
    }

    public function attachment()
    {
        return $this->hasOne(ChatMessageAttachment::class);
    }

    public function reactions()
    {
        return $this->hasMany(ChatMessageReaction::class);
    }

    public function deletions()
    {
        return $this->hasMany(ChatMessageDeletion::class);
    }

    public function views()
    {
        return $this->hasMany(ChatMessageView::class);
    }
}
