<?php

namespace App\Providers;

use App\RepositoryEloquent\ProductPayment\ProductPaymentInterface;
use App\RepositoryEloquent\ProductPayment\ProductPaymentRepository;
use Illuminate\Support\ServiceProvider;

class ProductPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ProductPaymentInterface::class,
            ProductPaymentRepository::class
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
