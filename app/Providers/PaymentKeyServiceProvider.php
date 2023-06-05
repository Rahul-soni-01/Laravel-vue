<?php

namespace App\Providers;

use App\RepositoryEloquent\PaymentKey\PaymentKeyInterface;
use App\RepositoryEloquent\PaymentKey\PaymentKeyRepository;
use Illuminate\Support\ServiceProvider;

class PaymentKeyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            PaymentKeyInterface::class,
            PaymentKeyRepository::class
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
