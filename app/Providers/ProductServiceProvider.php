<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\RepositoryEloquent\Product\ProductInterface;
use App\RepositoryEloquent\Product\ProductRepository;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ProductInterface::class,
            ProductRepository::class
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
