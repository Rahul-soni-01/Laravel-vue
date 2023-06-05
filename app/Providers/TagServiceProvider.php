<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\RepositoryEloquent\Tag\TagInterface;
use App\RepositoryEloquent\Tag\TagRepository;

class TagServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            TagInterface::class,
            TagRepository::class
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
