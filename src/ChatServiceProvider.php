<?php

namespace Metafroliclabs\LaravelChat;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Metafroliclabs\LaravelChat\Contracts\ChatResponseContract;
use Metafroliclabs\LaravelChat\Services\Core\ChatResponseService;
use Metafroliclabs\LaravelChat\Services\Core\FileService;
use Metafroliclabs\LaravelChat\Console\InstallCommand;
use Metafroliclabs\LaravelChat\Middleware\EnsureChatFeatureAllowed;

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
        // Load package assets
        $this->loadRoutesFrom(__DIR__ . '/../routes/chat.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register middleware
        $this->setMiddleware();

        // Make assets publishable
        $this->setPublishes();

        // Rate limiting
        $this->configureRateLimiting();
    }

    protected function setMiddleware()
    {
        $this->app->booted(function () {
            $router = $this->app['router'];
            $router->aliasMiddleware('chat.access', EnsureChatFeatureAllowed::class);
        });
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

    protected function configureRateLimiting()
    {
        RateLimiter::for('chats', function ($request) {
            return Limit::perMinute(config('chat.rate_limits.chat_creation_per_minute', 20))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('messages', function ($request) {
            return Limit::perMinute(config('chat.rate_limits.messages_per_minute', 40))
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
