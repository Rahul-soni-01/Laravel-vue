<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\RepositoryEloquent\File\FileInterface;
use App\RepositoryEloquent\File\FileRepository;

class FileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            FileInterface::class,
            FileRepository::class
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
