<?php

namespace Metafroliclabs\LaravelChat\Controllers;

use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;

class BaseController extends Controller
{
    protected $response;

    public function __construct(ChatResponseContract $response)
    {
        $this->response = $response;
    }
}