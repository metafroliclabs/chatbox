<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

class BaseService
{
    protected $pagination;

    public function __construct()
    {
        $requestPerPage = config('chat.paginate_records', 25);
        $this->pagination = $requestPerPage;
    }
}