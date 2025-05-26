<?php

namespace Metafroliclabs\LaravelChat;

use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/chat.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // $this->publishes([
        //     __DIR__ . '/../database/migrations/' => database_path('migrations')
        // ], 'chat-migrations');
    }
}