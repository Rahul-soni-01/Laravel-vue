<?php

namespace App\Providers;

use App\RepositoryEloquent\FanUser\FanUserInterface;
use App\RepositoryEloquent\FanUser\FanUserRepository;
use Illuminate\Support\ServiceProvider;

class FanUserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            FanUserInterface::class,
            FanUserRepository::class
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
