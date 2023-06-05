<?php

namespace App\Providers;

use App\RepositoryEloquent\Message\MessageInterface;
use App\RepositoryEloquent\Message\MessageRepository;
use Illuminate\Support\ServiceProvider;

class MessageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            MessageInterface::class,
            MessageRepository::class,
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
