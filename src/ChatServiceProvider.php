<?php

namespace Metafroliclabs\LaravelChat;

use Illuminate\Support\ServiceProvider;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Services\Core\ChatResponseService;
use Metafroliclabs\LaravelChat\Services\Core\FileService;
use Metafroliclabs\LaravelChat\Console\InstallCommand;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChatResponseContract::class, ChatResponseService::class);

        $this->app->singleton(FileService::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/chat.php', 'chat');

        // Register commands
        $this->commands([
            InstallCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/chat.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        $this->setPublishes();
    }

    protected function setPublishes()
    {
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
