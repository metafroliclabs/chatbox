<?php

namespace Metafroliclabs\LaravelChat\Traits;

trait HasUser
{
    /**
     * Get the user that owns the model.
     */
    public function user()
    {
        $UserModel = config('chat.user.model', \App\Models\User::class);
        return $this->belongsTo($UserModel);
    }
}