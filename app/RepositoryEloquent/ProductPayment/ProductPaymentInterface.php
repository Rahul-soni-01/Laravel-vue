<?php

namespace App\RepositoryEloquent\ProductPayment;

use App\Http\Requests\ProductPayment\CreateRequest;
use Illuminate\Http\Client\Request;

interface ProductPaymentInterface
{
    /**
     * @param CreateRequest $request
     * @return mixed
     */
    public function createProductPayment(CreateRequest $request);

    public function checkUserRegisterProduct($userId, $productId);
}
