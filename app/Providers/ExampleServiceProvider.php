<?php

namespace App\Providers;

use App\RepositoryEloquent\Example\ExampleInterface;
use App\RepositoryEloquent\Example\ExampleRepository;
use Illuminate\Support\ServiceProvider;

class ExampleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ExampleInterface::class,
            ExampleRepository::class
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
