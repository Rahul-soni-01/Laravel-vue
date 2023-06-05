<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\RepositoryEloquent\ProductFavorite\ProductFavoriteInterface;
use App\RepositoryEloquent\ProductFavorite\ProductFavoriteRepository;

class ProductFavoriteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ProductFavoriteInterface::class,
            ProductFavoriteRepository::class
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
