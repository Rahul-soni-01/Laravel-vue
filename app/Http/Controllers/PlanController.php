<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Plan\CreateRequest;
use App\Http\Requests\Plan\RegisterPlanRequest;
use App\Services\PaymentStripe;
use App\RepositoryEloquent\PlanUser\PlanUserInterface;
use App\RepositoryEloquent\Product\ProductInterface;
use App\Services\UserService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\RepositoryEloquent\Plan\PlanInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\RepositoryEloquent\Fan\FanInterface;
use App\Services\FileService;
use App\RepositoryEloquent\FanUser\FanUserInterface;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use App\Services\CommonService;
use Illuminate\Support\Carbon;
use App\RepositoryEloquent\PaymentKey\PaymentKeyInterface;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendMailUserOutFan;
use App\Jobs\SendMailUserJoinFan;

class PlanController extends Controller
{
    /**
     * @var PlanInterface
     */
    private PlanInterface $planRepository;

    /**
     * @var PaymentKeyInterface
     */
    private PaymentKeyInterface $paymentKeyRepository;

    private ProductInterface $productRepository;

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @var FanUserInterface
     */
    private FanUserInterface $fanUserRepository;

    /**
     * @var NotificationHistoryInterface
     */
    private NotificationHistoryInterface $notificationHistoryRepository;

    /**
     * @var FanInterface
     */
    private FanInterface $fanRepository;

    /**
     * @var FileService
     */
    private FileService $service;

    /**
     * @var PlanUserInterface
     */
    private PlanUserInterface $planUserRepository;

    /**
     * @var PaymentStripe
     */
    private PaymentStripe $productPaymentStripe;

    /**
     * @var CommonService
     */
    private CommonService $commonService;

    /**
     * @param PlanInterface $planRepository
     * @param FanInterface $fanRepository
     * @param PlanUserInterface $planUserRepository
     * @param FileService $service
     * @param PaymentStripe $productPaymentStripe
     * @param NotificationHistoryInterface $notificationHistoryRepository
     * @param FanUserInterface $fanUserRepository
     * @param UserService $userService
     * @param CommonService $commonService
     * @param  PaymentKeyInterface $paymentKeyRepository
     */
    public function __construct(
        PlanInterface                $planRepository,
        ProductInterface             $productRepository,
        FanInterface                 $fanRepository,
        PlanUserInterface            $planUserRepository,
        PaymentStripe                $productPaymentStripe,
        FileService                  $service,
        NotificationHistoryInterface $notificationHistoryRepository,
        FanUserInterface             $fanUserRepository,
        UserService $userService,
        CommonService $commonService,
        PaymentKeyInterface $paymentKeyRepository
    ) {
        $this->planRepository = $planRepository;
        $this->productRepository = $productRepository;
        $this->fanRepository = $fanRepository;
        $this->planUserRepository = $planUserRepository;
        $this->service = $service;
        $this->productPaymentStripe = $productPaymentStripe;
        $this->notificationHistoryRepository = $notificationHistoryRepository;
        $this->fanUserRepository = $fanUserRepository;
        $this->userService = $userService;
        $this->commonService = $commonService;
        $this->paymentKeyRepository = $paymentKeyRepository;
    }

    /**
     * Create a new plan for fan club
     *
     * @param CreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request)
    {
        try {
            DB::beginTransaction();
            $fanOwner = $this->fanRepository->getByAuthor(auth()->user()->id);

            if (!$fanOwner) {
                return ResponseHelper::bad();
            }

            $fanId = $fanOwner->id;
            $urlPhoto = '';
            if ($request->has('photo') && $request->photo) {
                $urlPhoto = $this->service->storeFileToS3($request->photo, 'plan');
            }

            $params = [
                'fan_id' => $fanId,
                'photo' => $urlPhoto,
                'title' => Arr::get($request, 'title'),
                'sub_title' => Arr::get($request, 'sub_title'),
                'price' => Arr::get($request, 'price'),
                'price_year' => Arr::get($request, 'price_year'),
                // 'discount' => Arr::get($request, 'discount'),
                'note' => Arr::get($request, 'note'),
            ];

            if (!empty($request->type)) {
                $params['type'] = $request->type;
            }

            $createProductStripe = $this->productPaymentStripe->createProductStripe($request);

            $createPriceStripe = $this->productPaymentStripe->createPriceStripe($createProductStripe->id, $request);

            // if (!empty($request->discount)) {
            //     $createDiscountStripe = $this->productPaymentStripe->createDiscountStripe($request->discount);
            //     if ($createDiscountStripe) {
            //         $params['discount_code'] = $createDiscountStripe->id;
            //     }
            // }

            if ($createProductStripe && $createPriceStripe) {
                $params['pro_stripe_id'] = $createProductStripe->id;
                $params['price_stripe_id'] = $createPriceStripe->id;
                $params['product_stripe'] = json_encode($createProductStripe);
                $params['price_stripe'] = json_encode($createPriceStripe);
            }

            $create = $this->planRepository->createPlan($params, $urlPhoto, $fanId);

            if (!$create) {
                return ResponseHelper::bad();
            }
            DB::commit();
            return ResponseHelper::ok();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param CreateRequest $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function update(CreateRequest $request, $id)
    {
        try {
            $params = [
                'title' => Arr::get($request, 'title'),
                'sub_title' => Arr::get($request, 'sub_title'),
                'fan_id' => Arr::get($request, 'fan_id'),
                'price' => Arr::get($request, 'price'),
                'price_year' => Arr::get($request, 'price_year'),
                // 'discount' => Arr::get($request, 'discount'),
                'note' => Arr::get($request, 'note'),
            ];
            $urlPhoto = '';
            if ($request->has('photo') && $request->photo) {
                $urlPhoto = $this->service->storeFileToS3($request->photo, 'plan');
            }

            if ($urlPhoto) {
                $params['photo'] = $urlPhoto;
            }

            if (!empty($request->type)) {
                $params['type'] = $request->type;
            }

            $updateProduct = $this->productPaymentStripe->updatePlanStripe($id, $request);

            $plan = $this->planRepository->findPlan($id);


            // if (!empty($request->discount) && $plan->discount != $request->discount) {
            //     $createDiscountStripe = $this->productPaymentStripe->createDiscountStripe($request->discount);
            //     if ($createDiscountStripe) {
            //         $params['discount_code'] = $createDiscountStripe->id;
            //     }
            // }


            if ($updateProduct) {
                $params['pro_stripe_id'] = $updateProduct['product_id'];
                $params['price_stripe_id'] = $updateProduct['price_id'];
                $params['product_stripe'] = json_encode($updateProduct['product_stripe']);
                $params['price_stripe'] = json_encode($updateProduct['price_stripe']);
            }

            $update = $this->planRepository->updatePlan($params, $id, $urlPhoto);

            if (!$update) {
                return ResponseHelper::bad();
            }

            return ResponseHelper::ok();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function destroy($id)
    {
        $data = $this->planRepository->findPlan($id);
        if (!$data) {
            return ResponseHelper::bad();
        }
        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));

        $delete = $stripe->products->delete(
            $data->pro_stripe_id,
            []
        );

        if (!$delete) {
            return ResponseHelper::bad();
        }

        $this->planRepository->destroy($id);
        return ResponseHelper::ok();
    }

    /**
     * Get list plans user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function userListPlan(Request $request)
    {
        return ResponseHelper::ok($this->planRepository->userListPlan($request));
    }

    /**
     * Get list plans creator
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function creatorListPlan(Request $request)
    {
        return ResponseHelper::ok($this->planRepository->creatorListPlan($request));
    }

    /**
     * @param Request $request
     * @return Response;
     */
    public function userRegisterPlan(RegisterPlanRequest $request)
    {
        DB::beginTransaction();
        try {
            // Get detail plan
            $plan = $this->planRepository
                ->getByConditions(
                    [],
                    ['id' => $request->plan_id],
                    '',
                    [],
                    ['users'],

                )
                ->first();

            if (!$plan) {
                return ResponseHelper::bad();
            }

            if ((int)($plan->price)) {
                // Check payment key
                $paymentKey = $this->paymentKeyRepository->getByUser($request->token);
                if (!$paymentKey) {
                    return ResponseHelper::bad([], 'key incorrect');
                }
            }

            if ((int)($plan->price)) {
                $validator = Validator::make($request->all(), [
                    'type' => 'required|numeric'
                ]);

                if ($validator->fails()) {
                    return ResponseHelper::validationError('Validation error', [
                        $validator->errors()->getMessages()
                    ]);
                }
            }

            // Get detail fan from plan
            $fan = $this->fanRepository
                ->getByConditions(
                    [],
                    ['id' => $plan->fan_id],
                    '',
                    [],
                    ['users'],

                )
                ->first();
            if (!$fan) {
                return ResponseHelper::bad();
            }

            // $priceYear = (int)$plan->price * 12;
            // if (!empty($plan->discount)) {
            //     $priceYear = (int)$plan->price * 12 - $plan->discount;
            // }

            $dataUserPlan = $this->planUserRepository->findCondition(
                [
                    'plan_id' => $request->get('plan_id'),
                    'user_id' => auth()->user()->id
                ]
            );

            $paymentDate = Carbon::now();

            if ($request->type == 1) {
                $expiredDate = Carbon::parse($paymentDate)->addMonth(1)->format('Y-m-d H:i:s');
            } else {
                $expiredDate = Carbon::parse($paymentDate)->addMonth(6)->format('Y-m-d H:i:s');
            }

            if ($dataUserPlan) {
                if (Carbon::now() <= Carbon::parse($dataUserPlan->expired_date)) {
                    $paymentDate = $dataUserPlan->payment_date;
                    if ($request->type == 1) {
                        $expiredDate = Carbon::parse($dataUserPlan->expired_date)->addMonth(1)->format('Y-m-d H:i:s');
                    } else {
                        $expiredDate = Carbon::parse($dataUserPlan->expired_date)->addMonth(6)->format('Y-m-d H:i:s');
                    }
                }
            }
            $currentUser = auth()->user();

            // Update Plan_user
            $paramSyncPlan = [
                'status' => CommonDefine::PAYMENT_SUCCESS,
                'payment_price' => $request->type == 1 ? $plan->price : $plan->price_year,
                'payment_date' => $paymentDate,
                'expired_date' => $expiredDate,
                'type' => $request->type ?? 1,
                'plan_id' => $request->get('plan_id'),
                'reason' => null,
                'user_id' => $currentUser->id
            ];

            $this->planUserRepository->updateOrCreate(
                [
                    'plan_id' => $request->get('plan_id'),
                    'user_id' => $currentUser->id
                ],
                $paramSyncPlan
            );

            // Update fan_user
            $paramSyncFan = [
                'status' => CommonDefine::PAYMENT_SUCCESS,
                'fan_id' => $fan->id,
                'user_id' => $currentUser->id
            ];

            $this->fanUserRepository->updateOrCreate(
                [
                    'fan_id' => $fan->id,
                    'user_id' => $currentUser->id
                ],
                $paramSyncFan
            );

            // Send notify to creator
            if ($this->userService->checkUserNotifyStatus($fan->author_id)) {
                $notifyParams = [
                    'content' => $currentUser->name ? $currentUser->name : $currentUser->email . ' ファン登録しました',
                    'type' => CommonDefine::FOLLOW,
                    'user_id' => $fan->author_id,
                    'fan_id' => $fan->id,
                    'created_by' => $currentUser->id
                ];

                $this->notificationHistoryRepository->create($notifyParams);
            }
            DB::commit();

            dispatch(new SendMailUserJoinFan($currentUser->email, [
                'email' => $currentUser->name ? $currentUser->name : $currentUser->email,
                'fan_name' => $fan->title,
                'plan_name' => $plan->title,
            ]));

            // Update key payment
            $this->paymentKeyRepository->updateByCondition(
                [
                    'status' => CommonDefine::PAYMENT_KEY_DEACTIVE,
                    'user_id' => auth()->user()->id,
                    'key' => $request->get('token')
                ],
                [
                    'status' => CommonDefine::PAYMENT_KEY_ACTIVE
                ]
            );

            return ResponseHelper::ok();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function detail(Request $request)
    {
        $data = $this->planRepository->getDetail($request->route('id'));

        return $data;
    }

    /**
     * Create new plan user
     * @param \App\Http\Requests\PlanUser\CreateRequest $request
     * @return ResponseFactory|Response
     */
    public function createPlanUser(\App\Http\Requests\PlanUser\CreateRequest $request)
    {
        $data = $this->planUserRepository->createPlanUser($request);

        if (!$data) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok($data);
    }

    public function cancelPlanUser($id, Request $request)
    {
        try {
            DB::beginTransaction();
            $result = $this->planUserRepository->cancelPlanUser($id, $request);

            if (!$result) {
                return ResponseHelper::bad();
            }

            $currentUser = auth()->user();

            $plan = $this->planRepository->findOrFail($id);

            $this->fanUserRepository->updateByCondition(
                [
                    'user_id' => $currentUser->id,
                    'fan_id' => $plan->fan_id
                ],
                [
                    'status' => CommonDefine::PAYMENT_CANCELLED
                ]
            );
            DB::commit();

            dispatch(new SendMailUserOutFan($currentUser->email, [
                'email' => $currentUser->name ? $currentUser->name : $currentUser->email,
            ]));

            return ResponseHelper::ok();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * Check user in plan
     * @param Request $request
     * @return Response
     */
    public function checkUserInPlan(Request $request)
    {
        if ($request->author_id == auth()->user()->id) {
            return ResponseHelper::ok();
        }

        if (!empty($request->product_id)) {
            $product = $this->productRepository->findOrFail($request->product_id);

            if ($product && $product->price == 0) return ResponseHelper::ok();
        }

        $check = $this->commonService->checkUserInFan(auth()->user()->id, $request->get('plan_id'));

        if (!$check) {
            return ResponseHelper::forbidden();
        }

        return ResponseHelper::ok();
    }

    /**
     * Check user in plan
     * @param Request $request
     * @return Response
     */
    public function checkUserInPlanStreaming(Request $request)
    {
        if ($request->author_id == auth()->user()->id) {
            return ResponseHelper::ok();
        }

        $check = $this->commonService->checkUserInPlanStreaming(auth()->user()->id, $request->get('plan_ids'));

        if (!$check) {
            return ResponseHelper::forbidden();
        }

        return ResponseHelper::ok();
    }
}
