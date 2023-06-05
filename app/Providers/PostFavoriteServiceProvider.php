<?php

namespace App\Providers;

use App\RepositoryEloquent\PostFavorite\PostFavoriteInterface;
use App\RepositoryEloquent\PostFavorite\PostFavoriteRepository;
use Illuminate\Support\ServiceProvider;

class PostFavoriteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            PostFavoriteInterface::class,
            PostFavoriteRepository::class
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
