<?php

namespace Metafroliclabs\LaravelChat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'chat_id',
        'user_id',
        'message',
        'replied_to_message_id',
        'deleted_at',
    ];

    protected $with = ['user', 'attachment', 'reactions', 'views'];

    // Relations
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
