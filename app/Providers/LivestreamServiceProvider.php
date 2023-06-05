<?php

namespace App\Providers;

use App\RepositoryEloquent\Livestream\LivestreamInterface;
use App\RepositoryEloquent\Livestream\LivestreamRepository;
use Illuminate\Support\ServiceProvider;

class LivestreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            LivestreamInterface::class,
            LivestreamRepository::class
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
