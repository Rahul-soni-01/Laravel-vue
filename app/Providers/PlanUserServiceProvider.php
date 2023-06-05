<?php

namespace App\Providers;

use App\RepositoryEloquent\PlanUser\PlanUserInterface;
use App\RepositoryEloquent\PlanUser\PlanUserRepository;
use Illuminate\Support\ServiceProvider;

class PlanUserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            PlanUserInterface::class,
            PlanUserRepository::class
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
