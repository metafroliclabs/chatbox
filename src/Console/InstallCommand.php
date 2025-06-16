<?php

namespace Metafroliclabs\LaravelChat\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'chat:install {--force : Force the installation even if already installed}';
    protected $description = 'Install the Laravel Chat package';

    public function handle()
    {
        $this->info('Installing Laravel Chat Package...');

        if ($this->isInstalled() && !$this->option('force')) {
            $this->error('Package is already installed. Use --force to reinstall.');
            return;
        }

        $this->publishConfig();
        $this->createStorageLink();
        // $this->publishMigrations();
        $this->runMigrations();

        $this->info('Laravel Chat Package installed successfully!');
        // $this->info('Please run "php artisan migrate" to create the database tables.');
    }

    protected function isInstalled()
    {
        return File::exists(config_path('chat.php'));
    }

    protected function publishConfig()
    {
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--provider' => 'Metafroliclabs\LaravelChat\ChatServiceProvider',
            '--tag' => 'chat-config',
            '--force' => $this->option('force')
        ]);
    }

    // protected function publishMigrations()
    // {
    //     $this->info('Publishing migrations...');
    //     $this->call('vendor:publish', [
    //         '--provider' => 'Metafroliclabs\LaravelChat\ChatServiceProvider',
    //         '--tag' => 'chat-migrations',
    //         '--force' => $this->option('force')
    //     ]);
    // }

    protected function runMigrations()
    {
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->info('Running migrations...');
            $this->call('migrate');
        }
    }

    protected function createStorageLink()
    {
        // if ($this->confirm('Do you want to create a storage link for file uploads?', true)) {}
        $this->info('Creating storage link...');
        $this->call('storage:link');
    }
}
