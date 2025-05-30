<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

class BaseService
{
    protected $pagination;
    protected $per_page;

    public function __construct()
    {
        $pagination = config('chat.pagination', true);
        $requestPerPage = config('chat.pagination_records', 25);

        $this->pagination = $pagination;
        $this->per_page = $requestPerPage;
    }
}