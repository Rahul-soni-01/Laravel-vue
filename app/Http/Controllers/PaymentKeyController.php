<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\RepositoryEloquent\PaymentKey\PaymentKeyInterface;

class PaymentKeyController extends Controller
{
    /**
     * @var  PaymentKeyInterface
     */
    private PaymentKeyInterface $paymentKeyRepository;

    /**
     * @param PaymentKeyInterface $paymentKeyRepository
     */
    public function __construct(PaymentKeyInterface $paymentKeyRepository)
    {
        $this->paymentKeyRepository = $paymentKeyRepository;
    }

    /**
     * @return ResponseFactory|Response
     */
    public function getByUser()
    {
        return ResponseHelper::ok($this->paymentKeyRepository->getByUser());
    }

    /**
     * @return ResponseFactory|Response
     */
    public function createRandomKey(Request $request)
    {
        $create = $this->paymentKeyRepository->createRandomKey();
        if (!$create) return ResponseHelper::bad();

        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));

        if ($request->has('success_url')) {
            $params = [
                'line_items' => $request->line_items,
                'mode' => 'payment',
                'success_url' => $request->success_url . $create->key,
                'cancel_url' => $request->cancel_url,
            ];

            if ($request->has('discounts')) {
                $params['discounts'] = $request->discounts;
            }

            $session = $stripe->checkout->sessions->create($params);

            if ($session) {
                $create['session_id'] = $session->id;
            }
        }

        return ResponseHelper::ok($create);
    }

    /**
     * @return ResponseFactory|Response
     */
    public function updateStatusKey(Request $request)
    {
        $update = $this->paymentKeyRepository->updateByCondition(
            [
                'status' => CommonDefine::PAYMENT_KEY_DEACTIVE,
                'user_id' => auth()->user()->id,
                'key' => $request->get('token')
            ],
            [
                'status' => CommonDefine::PAYMENT_KEY_ACTIVE
            ]
        );

        if (!$update) return ResponseHelper::bad();

        return ResponseHelper::ok();
    }
}
