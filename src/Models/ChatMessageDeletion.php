<?php

namespace Metafroliclabs\LaravelChat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessageDeletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_message_id',
        'user_id',
    ];

    // Relations
    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
