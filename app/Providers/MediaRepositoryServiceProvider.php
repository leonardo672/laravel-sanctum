<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\MediaRepositoryInterface;
use App\Repositories\MediaRepository;

class MediaRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(
            MediaRepositoryInterface::class,
            MediaRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Optional: publish config files if needed
    }
}