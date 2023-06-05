<?php

namespace App\Providers;

use App\RepositoryEloquent\ParentCategory\ParentCategoryInterface;
use App\RepositoryEloquent\ParentCategory\ParentCategoryRepository;
use Illuminate\Support\ServiceProvider;

class ParentCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ParentCategoryInterface::class,
            ParentCategoryRepository::class
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
