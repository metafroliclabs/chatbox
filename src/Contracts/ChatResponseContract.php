<?php

namespace Metafroliclabs\LaravelChat\Contracts;

interface ChatResponseContract
{
    public function success($data = [], int $code = 200);
    
    public function fail($errors = [], int $code = 422);
}