<?php

namespace App\Services;


use App\Helpers\ResponseHelper;
use App\RepositoryEloquent\Plan\PlanInterface;
use App\RepositoryEloquent\Product\ProductInterface;
use Illuminate\Support\Facades\Log;
use App\Helpers\FormatHelper;

class PaymentStripe extends BaseService
{
    public $productRepository;
    public $planRepository;

    /**
     * @param ProductInterface $productRepository
     */
    public function __construct(
        ProductInterface $productRepository,
        PlanInterface $planRepository,
    ) {
        $this->productRepository = $productRepository;
        $this->planRepository = $planRepository;
    }

    /**
     * @param $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Stripe\Product
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createProductStripe($request)
    {
        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));

        $data = $stripe->products->create(
            [
                'name' => $request->get('title')
            ]
        );

        if (!$data) {
            Log::error('------------------ create product err ---------------') . PHP_EOL;
            return ResponseHelper::bad([], 'create data false');
        }
        Log::info('------------------ create product success ---------------') . PHP_EOL;
        return $data;
    }

    /**
     * @param $productId
     * @param $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Stripe\Price
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createPriceStripe($productId, $request)
    {
        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));
        $data = $stripe->prices->create([
            'unit_amount' => $request->price,
            'currency' => 'jpy',
            'product' => $productId,
        ]);

        $stripe->products->update(
            $productId,
            [
                'default_price' => $data->id
            ]
        );

        if (!$data) {
            Log::error('------------------ create price err ---------------') . PHP_EOL;
            return ResponseHelper::bad([], 'create data false');
        }
        Log::info('------------------ create price success ---------------') . PHP_EOL;
        return $data;
    }

    public function createDiscountStripe($discount)
    {
        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));
        $data = $stripe->coupons->create([
            'id' => FormatHelper::generateRandomString(),
            'currency' => 'jpy',
            'amount_off' => $discount,
        ]);

        if (!$data) {
            Log::error('------------------ create discount code err ---------------') . PHP_EOL;
            return ResponseHelper::bad([], 'create data false');
        }
        Log::info('------------------ create discount code success ---------------') . PHP_EOL;
        return $data;
    }

    /**
     * @param $productId
     * @param $request
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function updateProductStripe($productId, $request)
    {
        $product = $this->productRepository->findProduct($productId);
        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));

        $dataPrice = $stripe->prices->create([
            'unit_amount' => $request->price,
            'currency' => 'jpy',
            'product' => $product->pro_stripe_id,
        ]);

        $updateProduct = $stripe->products->update(
            $product->pro_stripe_id,
            [
                'name' => $request->get('title'),
                'default_price' => $dataPrice->id
            ]
        );

        return [
            'product_id' => $updateProduct->id,
            'price_id' => $dataPrice->id,
            'product_stripe' => $updateProduct,
            'price_stripe' => $dataPrice
        ];
    }

    /**
     * @param $plantId
     * @param $request
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function updatePlanStripe($plantId, $request)
    {
        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));

        $plan = $this->planRepository->findPlan($plantId);
        $dataPrice = $stripe->prices->create([
            'unit_amount' => $request->price,
            'currency' => 'jpy',
            'product' => $plan->pro_stripe_id,
        ]);

        $updatePlan = $stripe->products->update(
            $plan->pro_stripe_id,
            [
                'name' => $request->get('title'),
                'default_price' => $dataPrice->id
            ]
        );

        return [
            'product_id' => $updatePlan->id,
            'price_id' => $dataPrice->id,
            'product_stripe' => $updatePlan,
            'price_stripe' => $dataPrice
        ];
    }
}
