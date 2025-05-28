<?php

namespace Metafroliclabs\LaravelChat;

use Illuminate\Support\ServiceProvider;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Services\ChatResponseService;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChatResponseContract::class, ChatResponseService::class);

        $this->mergeConfigFrom(__DIR__.'/../config/chat.php', 'chat');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/chat.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Migrations
        // $this->publishes([
        //     __DIR__ . '/../database/migrations/' => database_path('migrations')
        // ], 'chat-migrations');

        // Config
        $this->publishes([
            __DIR__ . '/../config/chat.php' => config_path('chat.php')
        ], 'chat-config');
    }
}