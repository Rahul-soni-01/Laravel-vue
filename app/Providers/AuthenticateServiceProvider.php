<?php

namespace App\Providers;

use App\RepositoryEloquent\Auth\AuthInterface;
use App\RepositoryEloquent\Auth\AuthRepository;
use Illuminate\Support\ServiceProvider;

class AuthenticateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            AuthInterface::class,
            AuthRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
