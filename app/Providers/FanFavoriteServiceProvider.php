<?php

namespace App\Providers;

use App\RepositoryEloquent\FanFavorite\FanFavoriteInterface;
use App\RepositoryEloquent\FanFavorite\FanFavoriteRepository;
use Illuminate\Support\ServiceProvider;

class FanFavoriteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            FanFavoriteInterface::class,
            FanFavoriteRepository::class
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
