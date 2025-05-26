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

    protected $fillable = [
        'type',
        'name',
        'image'
    ];

    // Relationship
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_users');
    }

    // Accessors
    public function getImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}