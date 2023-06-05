<?php

namespace App\RepositoryEloquent\ProductPayment;

use App\Define\CommonDefine;
use App\Http\Requests\ProductPayment\CreateRequest;
use App\Models\ProductPayment;
use App\RepositoryEloquent\BaseRepository;

class ProductPaymentRepository extends BaseRepository implements ProductPaymentInterface
{
    public function model()
    {
        return ProductPayment::class;
    }

    /**
     * @param CreateRequest $request
     * @return void
     */
    public function createProductPayment(CreateRequest $request)
    {
        $paramsCreate = array_merge($request->validated(), [
            'user_id' => auth()->user()->id,
            'status' => CommonDefine::PAYMENT_SUCCESS,
        ]);

        return $this->model->create($paramsCreate);
    }

    public function checkUserRegisterProduct($userId, $productId)
    {
        $data = $this->model->where('user_id', $userId)->where('product_id', $productId)->get();

        return $data;
    }
}
