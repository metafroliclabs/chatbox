<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

use Illuminate\Support\Facades\DB;

class BaseService
{
    protected $pagination;
    protected $per_page;

    public function __construct()
    {
        $pagination = config('chat.pagination', true);
        $requestPerPage = config('chat.per_page', 25);

        $this->pagination = $pagination;
        $this->per_page = $requestPerPage;
    }

    protected function getNameColumn()
    {
        $cols = config('chat.user.name_cols', []);
        if (count($cols) > 1) {
            $str = implode(", ' ', ", $cols);
            return DB::raw("CONCAT($str)");
        }
        return $cols[0];
    }

    protected function getFullName($user)
    {
        $fullname = "";
        $cols = config('chat.user.name_cols', []);
        if ($user && !empty($cols)) {
            $fullname = collect($cols)
                ->map(fn($col) => $user->{$col} ?? '')
                ->filter() // remove null or empty values
                ->implode(' ');
        }
        return $fullname;
    }
}