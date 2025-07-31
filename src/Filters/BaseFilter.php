<?php

namespace Metafroliclabs\LaravelChat\Filters;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Metafroliclabs\LaravelChat\Models\Chat;

abstract class BaseFilter
{
    protected $userId;
    protected $request;

    public function apply(Builder $query, Request $request): Builder
    {
        $this->userId = auth()->id();
        $this->request = $request;

        // Determine if any user filters are active
        if ($this->hasActiveUserFilters()) 
        {
            $query->where(function ($q) {
                $q->where('type', Chat::PRIVATE)
                    ->whereHas('users', function ($q2) 
                    {
                        $q2->where('user_id', '!=', $this->userId);
                        $this->applyUserFilters($q2);
                    });
            });
        }

        return $query;
    }

    /**
     * Users override this to apply filters like gender, role etc.
     */
    abstract protected function applyUserFilters(Builder $query): void;

    /**
     * Override this if you want more control over active filters
     */
    protected function hasActiveUserFilters(): bool
    {
        return false;
    }
}
