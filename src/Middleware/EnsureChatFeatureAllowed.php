<?php

namespace Metafroliclabs\LaravelChat\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metafroliclabs\LaravelChat\Exceptions\ChatException;
use Metafroliclabs\LaravelChat\Support\ChatAccessPolicy;

class EnsureChatFeatureAllowed
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $action = $route->getActionName(); // e.g., App\Http\Controllers\ChatController@create

        if (str_contains($action, '@')) {
            [$fullController, $method] = explode('@', $action);
            $controller = class_basename($fullController);

            if (!ChatAccessPolicy::isAllowed($controller, $method)) {
                throw new ChatException('This action is not allowed in universal chat mode.');
            }
        }

        return $next($request);
    }
}
