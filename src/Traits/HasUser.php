<?php

namespace Metafroliclabs\LaravelChat\Traits;

use App\Models\User;

trait HasUser
{
    /**
     * Get the user that owns the model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}