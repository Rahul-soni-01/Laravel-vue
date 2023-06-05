<?php

namespace App\RepositoryEloquent\PaymentKey;

use App\Define\CommonDefine;
use App\Models\PaymentKey;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Str;

class PaymentKeyRepository extends BaseRepository implements PaymentKeyInterface
{
    public function model()
    {
        return PaymentKey::class;
    }

    /**
     * @return mixed
     */
    public function getByUser($token)
    {
        return $this->model
            ->where('user_id', auth()->user()->id)
            ->where('key', $token)
            ->where('status', CommonDefine::PAYMENT_KEY_DEACTIVE)
            ->first();
    }

    /**
     * @return mixed
     */
    public function createRandomKey()
    {
        $key = Str::random(20);
        $params = [
            'key' => $key,
            'user_id' => auth()->user()->id,
            'status' => CommonDefine::PAYMENT_KEY_DEACTIVE
        ];

        return $this->model->create($params);
    }
}
