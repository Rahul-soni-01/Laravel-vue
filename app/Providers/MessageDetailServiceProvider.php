<?php

namespace App\Providers;

use App\RepositoryEloquent\MessageDetail\MessageDetailInterface;
use App\RepositoryEloquent\MessageDetail\MessageDetailRepository;
use Illuminate\Support\ServiceProvider;

class MessageDetailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            MessageDetailInterface::class,
            MessageDetailRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
