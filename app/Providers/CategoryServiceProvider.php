<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\RepositoryEloquent\Category\CategoryRepository;
use App\RepositoryEloquent\Category\CategoryInterface;

class CategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            CategoryInterface::class,
            CategoryRepository::class
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
