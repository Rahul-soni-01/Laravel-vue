<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use App\RepositoryEloquent\Fan\FanInterface;
use App\RepositoryEloquent\ProductFavorite\ProductFavoriteInterface;
use App\RepositoryEloquent\ProductPayment\ProductPaymentInterface;
use App\RepositoryEloquent\Tag\TagInterface;
use App\Services\PaymentStripe;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use App\RepositoryEloquent\Product\ProductInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Services\FileService;
use App\RepositoryEloquent\File\FileInterface;
use App\Http\Requests\Product\CreateRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Requests\Product\ListProductRequest;
use App\Http\Requests\Product\UpdateStatusRequest;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use App\Services\CommonService;
use App\RepositoryEloquent\PaymentKey\PaymentKeyInterface;
use App\RepositoryEloquent\User\UserInterface;
use Illuminate\Support\Carbon;
use App\Services\UserService;
use App\RepositoryEloquent\FanUser\FanUserInterface;
use App\RepositoryEloquent\PlanUser\PlanUserInterface;
use App\RepositoryEloquent\Plan\PlanInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\SendMailUserJoinFan;


class ProductController extends Controller
{
    /**
     *@var ProductInterface
     */
    private ProductInterface $productRepository;

    private UserInterface $userRepository;

    private FanUserInterface $fanUserRepository;

    private PlanUserInterface $planUserRepository;

    private PlanInterface $planRepository;

    /**
     * @var FanInterface
     */
    private FanInterface $fanRepository;

    /**
     *@var FileService
     */
    private FileService $fileService;

    /**
     *@var FileInterface
     */
    private FileInterface $fileRepository;

    /**
     *@var TagInterface
     */
    private TagInterface $tagRepository;

    /**
     *@var ProductFavoriteInterface
     */
    private ProductFavoriteInterface $productFavoriteRepository;

    /**
     * @var ProductPaymentInterface
     */
    private ProductPaymentInterface $productPaymentRepository;

    /**
     *@var PaymentStripe
     */
    private PaymentStripe $productPaymentStripe;

    /**
     * @var ProductService
     */
    private ProductService $productService;

    /**
     * @var CommonService
     */
    private CommonService $commonService;

    private NotificationHistoryInterface $notificationHistoryRepository;

    /**
     * @var PaymentKeyInterface
     */
    private PaymentKeyInterface $paymentKeyRepository;

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @param ProductInterface $productRepository
     * @param FileService $fileService
     * @param FileInterface $fileRepository
     * @param TagInterface $tagRepository
     * @param ProductFavoriteInterface $productFavoriteRepository
     * @param PaymentStripe $productPaymentStripe
     * @param ProductPaymentInterface $productPaymentRepository
     * @param FanInterface $fanRepository
     * @param CommonService $commonService
     * @param PaymentKeyInterface $paymentKeyRepository
     * @param UserService $userService
     */
    public function __construct(
        ProductInterface $productRepository,
        FileService $fileService,
        FileInterface $fileRepository,
        ProductFavoriteInterface $productFavoriteRepository,
        TagInterface $tagRepository,
        ProductPaymentInterface $productPaymentRepository,
        PaymentStripe $productPaymentStripe,
        ProductService $productService,
        NotificationHistoryInterface $notificationHistoryRepository,
        FanInterface $fanRepository,
        CommonService $commonService,
        PaymentKeyInterface $paymentKeyRepository,
        UserInterface $userRepository,
        FanUserInterface $fanUserRepository,
        PlanUserInterface $planUserRepository,
        PlanInterface $planRepository,
        UserService $userService
    ) {
        $this->productRepository = $productRepository;
        $this->fileService = $fileService;
        $this->fileRepository = $fileRepository;
        $this->productFavoriteRepository = $productFavoriteRepository;
        $this->tagRepository = $tagRepository;
        $this->productPaymentRepository = $productPaymentRepository;
        $this->productPaymentStripe = $productPaymentStripe;
        $this->productService  = $productService;
        $this->fanRepository = $fanRepository;
        $this->notificationHistoryRepository = $notificationHistoryRepository;
        $this->commonService = $commonService;
        $this->paymentKeyRepository = $paymentKeyRepository;
        $this->userRepository = $userRepository;
        $this->fanUserRepository = $fanUserRepository;
        $this->planUserRepository = $planUserRepository;
        $this->planRepository = $planRepository;
        $this->userService = $userService;
    }

    public function getPreSigned(Request $request)
    {
        $client = Storage::disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');
        $fileName = Str::random(20) . '_' . $request->file_name;
        $filePath =  'file_products/' . $fileName;
        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $filePath
        ]);

        $request = $client->createPresignedRequest($command, '+20 minutes');

        return [
            'file_path' => $filePath,
            'pre_signed' => (string) $request->getUri(),
        ];
    }

    public function test(){

        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $this->productRepository->getListCreator($request);
        return ResponseHelper::ok($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request)
    {
        return "dasds";
        DB::beginTransaction();
        try {
            $params = $request->only([
                'title',
                'content',
                'category_id',
                'price',
                'is_public',
                'auto_public',
                'date_public',
                'type',
                'status',
                'plan_id',
                'price_type',
                'brand_id',
                'plan_id'
            ]);

            // $createProductStripe = $this->productPaymentStripe->createProductStripe($request);
            // $createPriceStripe = $this->productPaymentStripe->createPriceStripe($createProductStripe->id, $request);
            // if ($createProductStripe && $createPriceStripe) {
            //     $params['pro_stripe_id'] = $createProductStripe->id;
            //     $params['price_stripe_id'] = $createPriceStripe->id;
            //     $params['product_stripe'] = json_encode($createProductStripe);
            //     $params['price_stripe'] = json_encode($createPriceStripe);
            // }

            if ($request->thumbnail) {
                $params['thumbnail_url'] = $this->fileService->storeFileToS3($request->thumbnail, 'file_products');
            }

            $userLogin = auth()->user();
            $params['author_id'] = $userLogin->id;
            if (!empty($params['is_public']) && $params['is_public'] == 1) {
                $params['date_public'] = Carbon::now();
            }
            $createProduct = $this->productRepository->createProduct($params);

            if (!$createProduct) {
                return ResponseHelper::bad();
            }

            if ($request->type == CommonDefine::PRODUCT_VIDEO) {
                $paramsFile = [
                    'url' => $request->file_url,
                    'user_id' => auth()->user()->id,
                    'name' => $request->file_name,
                ];
                $insertedVideo = $this->fileRepository->InsertFile($paramsFile);
                if (!$insertedVideo) {
                    return ResponseHelper::bad();
                }
                $createProduct->files()->sync([$insertedVideo->id]);
            } elseif ($request->type == CommonDefine::PRODUCT_IMAGE) {
                $fileIds = [];
                foreach ($request->file('files') as $file) {
                    $urlFile = $this->fileService->storeFileToS3($file, 'file_products');
                    $paramsFile = [
                        'url' => $urlFile,
                        'user_id' => $userLogin->id,
                        'name' => $file->getClientOriginalName()
                    ];
                    $insertedImage = $this->fileRepository->InsertFile($paramsFile);
                    if (!$insertedImage) {
                        return ResponseHelper::bad();
                    }
                    $fileIds[] = $insertedImage->id;
                }
                $createProduct->files()->sync($fileIds);
            }

            if ($request->tags) {
                $listTagId = [];
                foreach ($request->tags as $tag) {
                    $tagName = $this->tagRepository->findTagName($tag);
                    $tagId = $tagName !== null ? $tagName->id : null;
                    if ($tagName == null) {
                        $dataTag = [
                            "author_id" => auth()->user()->id,
                            "name" => $tag,
                        ];
                        $tagCreate = $this->tagRepository->create($dataTag);
                        $tagId = $tagCreate->id;
                    }

                    $listTagId[] = $tagId;
                }
                $createProduct->tags()->sync($listTagId);
            }

            $fan = $this->fanRepository->getByAuthor(auth()->user()->id);

            if ($fan && $createProduct->is_public) {
                $fan->users->map(function ($user) use ($fan, $createProduct) {
                    if ($user->pivot->status && $user->is_notification) {
                        $notifyParams = [
                            'content'  => $fan->title . ' 新しい投稿を投稿しました',
                            'type' => CommonDefine::NEW_PRODUCT_OR_POST,
                            'user_id' => $user->id,
                            'product_id' => $createProduct->id,
                            'fan_id' => $fan->id,
                            'created_by' => auth()->user()->id
                        ];

                        $this->notificationHistoryRepository->create($notifyParams);
                    }
                });
            }
            DB::commit();

            return ResponseHelper::ok($createProduct);
        } catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $params = $request->only([
                'title',
                'content',
                'category_id',
                'price',
                'is_public',
                'auto_public',
                'date_public',
                'type',
                'status',
                'brand_id',
                'plan_id'
            ]);

            if ($params['plan_id'] == 0) {
                $params['plan_id'] = null;
            }

            // $updateProduct = $this->productPaymentStripe->updateProductStripe($id, $request);
            // if ($updateProduct) {
            //     $params['pro_stripe_id'] = $updateProduct['product_id'];
            //     $params['price_stripe_id'] = $updateProduct['price_id'];
            //     $params['product_stripe'] = json_encode($updateProduct['product_stripe']);
            //     $params['price_stripe'] = json_encode($updateProduct['price_stripe']);
            // }

            if ($request->thumbnail) {
                $params['thumbnail_url'] = $this->fileService->storeFileToS3($request->thumbnail, 'file_products');
            }

            $userLogin = auth()->user();
            $params['author_id'] = $userLogin->id;
            if (!empty($params['is_public']) && $params['is_public'] == 1) {
                $params['date_public'] = Carbon::now();
            }
            $updateProduct = $this->productRepository->updateProduct($params, $id);
            if (!$updateProduct) {
                return ResponseHelper::bad();
            }

            if ($request->has('delete_ids')) {
                $updateProduct->files()->detach(Arr::get($request, 'delete_ids'));
            }

            if ($request->file_url && $request->type == CommonDefine::PRODUCT_VIDEO) {
                $paramsFile = [
                    'url' => $request->file_url,
                    'user_id' => auth()->user()->id,
                    'name' => $request->file_name,
                ];
                $insertedVideo = $this->fileRepository->InsertFile($paramsFile);
                if (!$insertedVideo) {
                    return ResponseHelper::bad();
                }
                $updateProduct->files()->sync([$insertedVideo->id]);
            } elseif ($request->type == CommonDefine::PRODUCT_IMAGE) {
                $fileIds = [];
                foreach ($request->file('files') as $file) {
                    $urlFile = $this->fileService->storeFileToS3($file, 'file_products');
                    $paramsFile = [
                        'url' => $urlFile,
                        'user_id' => $userLogin->id,
                        'name' => $file->getClientOriginalName()
                    ];
                    $insertedImage = $this->fileRepository->InsertFile($paramsFile);
                    if (!$insertedImage) {
                        return ResponseHelper::bad();
                    }
                    $fileIds[] = $insertedImage->id;
                }
                $updateProduct->files()->syncWithoutDetaching($fileIds);
            }

            if ($request->tags) {
                $listTagId = [];
                foreach ($request->tags as $tag) {
                    $tagName = $this->tagRepository->findTagName($tag);
                    $tagId = $tagName !== null ? $tagName->id : null;
                    if ($tagName == null) {
                        $dataTag = [
                            "author_id" => auth()->user()->id,
                            "name" => $tag,
                        ];
                        $tagCreate = $this->tagRepository->create($dataTag);
                        $tagId = $tagCreate->id;
                    }

                    $listTagId[] = $tagId;
                }
                $updateProduct->tags()->sync($listTagId);
            } else {
                $updateProduct->tags()->sync([]);
            }

            DB::commit();
            return ResponseHelper::ok($updateProduct);
        } catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * Update product's public status.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProductPublic(Request $request)
    {
        $data = $this->productRepository->updateProductPublic($request);
        if (!$data) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listProductAdmin(ListProductRequest $request)
    {
        $data = $this->productRepository->listProductAdmin($request);

        return ResponseHelper::ok($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function updateStatus(UpdateStatusRequest $request)
    {
        $adminUpdate = $this->productRepository->updateStatus($request);

        if (!$adminUpdate) {
            return  ResponseHelper::bad();
        }

        return  ResponseHelper::ok();
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function destroy($id)
    {
        $dataProduct = $this->productRepository->findProduct($id);
        if (!$dataProduct) {
            return ResponseHelper::bad();
        }
        $stripe = new \Stripe\StripeClient(config('payment.api_key_stripe'));

        $delete = $stripe->products->delete(
            $dataProduct->pro_stripe_id,
            []
        );
        if (!$delete) {
            return ResponseHelper::bad();
        }
        $this->productRepository->deleteProduct($id);
        return ResponseHelper::ok();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listProductUser(Request $request)
    {
        $data = $this->productRepository->getListProductPublic($request);
        return ResponseHelper::ok($data);
    }

    /**
     * @param $id
     * @return Response
     */
    public function creatorDestroy($id)
    {
        $this->productRepository->deleteProduct($id);
        return ResponseHelper::ok();
    }

    /**
     * @param $id
     * @return Response
     */
    public function detailProductUser($id)
    {
        $userCurrent = auth()->user();
        $product = $this->productRepository->find($id);

        if (!$product) {
            return ResponseHelper::bad([], 'データが見つかりません');
        }

        $data = $this->productRepository->getDetail($id);

        // check current user is author of product
        if ($product->author_id == $userCurrent->id) {
            $data->check_user_register = 1;
            $data->is_user_plan = 1;
            return ResponseHelper::ok($data);
        }

        $checkUserOfPlan = $this->planUserRepository->findCondition(
            [
                'user_id' => $userCurrent->id,
                'plan_id' => $product->plan_id,
                ['expired_date', '>=', Carbon::now()->format('Y-m-d')]
            ]
        );

        if (!$checkUserOfPlan && $data->plan_id) {
            $data->is_user_plan = 0;
            $data->check_user_register = 0;

            unset($data->files);
            $this->productRepository->updateView($id);
            return ResponseHelper::ok($data);
        } else {
            $data->is_user_plan = 1;
        }

        $checkUser = $this->productPaymentRepository->checkUserRegisterProduct($userCurrent->id, $data->id);
        if ($checkUser->count() == 0 && $data->price != 0) {
            $data->check_user_register = 0;
            unset($data->files);
        } else {
            $data->check_user_register = 1;
        }

        // updateView
        $this->productRepository->updateView($id);

        return ResponseHelper::ok($data);
    }

    public function detailProductUserWithoutAuth($id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) return ResponseHelper::bad([], 'データが見つかりません');

        $data = $this->productRepository->getDetail($id);
        $data->is_user_plan = 0;
        $data->check_user_register = 0;

        unset($data->files);
        $this->productRepository->updateView($id);
        return ResponseHelper::ok($data);
    }

    /**
     * @param $id
     * @return Response
     */
    public function detailProductCreator($id)
    {
        return ResponseHelper::ok($this->productRepository->getDetail($id));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function detailProductAdmin($id)
    {
        $data = $this->productRepository->getDetailAdmin($id);
        return ResponseHelper::ok($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws Exception
     */
    public function favoriteProduct(Request $request)
    {
        return ResponseHelper::ok($this->productService->favoriteProduct($request));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws Exception
     */
    public function getFavoriteProduct(Request $request)
    {
        try {
            $data = $this->productFavoriteRepository->getFavoriteProduct($request);
            if (!$data) {
                return ResponseHelper::bad();
            }
            return ResponseHelper::ok($data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws Exception
     */
    public function productView(Request $request)
    {
        try {
            $data = $this->productRepository->productView($request);
            if (!$data) {
                return ResponseHelper::bad();
            }
            return ResponseHelper::ok($data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getFavoriteProducts(Request $request)
    {
        return ResponseHelper::ok($this->productRepository->getFavoriteProducts($request));
    }

    public function payment(Request $request)
    {
        \Log::info('CREDIX PAYMENT');
        $params = $request->all();
        \Log::info($params);

        if (!$params['result'] ||  $params['result'] != 'ok') return ResponseHelper::ok();

        if (empty($params['sendid'])) return ResponseHelper::ok();

        list($type, $userId, $id) = explode('_', $params['sendid']);

        try {
            if ($type == 'product') {
                // Get detail product
                $product = $this->productRepository->getByConditions([], ['id' => $id])->first();
                if (!$product) return ResponseHelper::ok();

                $user = $this->userRepository->getByConditions([], ['id' => $userId])->first();
                if (!$user) return ResponseHelper::ok();

                // Update product payment
                $paramSyncProduct = [
                    'status' => CommonDefine::PAYMENT_SUCCESS,
                    'payment_price' => $params['money'],
                    'payment_date' => Carbon::now(),
                    'product_id' => $product->id,
                    'user_id' => $user->id
                ];

                $this->productPaymentRepository->updateOrCreate(
                    [
                        'status' => CommonDefine::PAYMENT_CANCELLED,
                        'product_id' => $id,
                        'user_id' => $user->id
                    ],
                    $paramSyncProduct
                );

                // Send notify to creator
                if ($this->userService->checkUserNotifyStatus($product->author_id)) {
                    $notifyParams = [
                        'content' => $user->name ? $user->name : $user->email . ' ファン登録しました',
                        'type' => CommonDefine::FOLLOW,
                        'user_id' => $product->author_id,
                        'product_id' => $product->id,
                        'created_by' => $user->id
                    ];

                    $this->notificationHistoryRepository->create($notifyParams);
                }
            }

            if ($type == 'plan') {
                // Get detail plan
                $plan = $this->planRepository
                    ->getByConditions(
                        [],
                        ['id' => $id],
                        '',
                        [],
                        ['users'],
                    )
                    ->first();
                if (!$plan) return ResponseHelper::ok();

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
                if (!$fan) return ResponseHelper::ok();

                $user = $this->userRepository->getByConditions([], ['id' => $userId])->first();
                if (!$user) return ResponseHelper::ok();

                //register plan
                $dataUserPlan = $this->planUserRepository->findCondition(
                    [
                        'plan_id' => $id,
                        'user_id' => $user->id,
                    ]
                );

                $paymentDate = Carbon::now();

                if ($params['money'] == 1490) {
                    $expiredDate = Carbon::parse($paymentDate)->addMonth(1)->format('Y-m-d H:i:s');
                } else {
                    $expiredDate = Carbon::parse($paymentDate)->addMonth(6)->format('Y-m-d H:i:s');
                }

                if ($dataUserPlan) {
                    if (Carbon::now() <= Carbon::parse($dataUserPlan->expired_date)) {
                        $paymentDate = $dataUserPlan->payment_date;
                        if ($params['money'] == 1490) {
                            $expiredDate = Carbon::parse($dataUserPlan->expired_date)->addMonth(1)->format('Y-m-d H:i:s');
                        } else {
                            $expiredDate = Carbon::parse($dataUserPlan->expired_date)->addMonth(6)->format('Y-m-d H:i:s');
                        }
                    }
                }

                $typeP = 1; // 1: monthly, 2: yearly
                if ($params['money'] == 5400) {
                    $typeP = 2;
                }

                // Update Plan_user
                $paramSyncPlan = [
                    'status' => CommonDefine::PAYMENT_SUCCESS,
                    'payment_price' => $params['money'],
                    'payment_date' => $paymentDate,
                    'expired_date' => $expiredDate,
                    'type' => $typeP,
                    'plan_id' => $id,
                    'user_id' => $user->id,
                    'email' => $params['email'] ?? null,
                    'telno' => $params['telno'] ?? null,
                    'reason' => null
                ];

                $this->planUserRepository->updateOrCreate(
                    [
                        'plan_id' => $id,
                        'user_id' => $user->id
                    ],
                    $paramSyncPlan
                );

                // Update fan_user
                $paramSyncFan = [
                    'status' => CommonDefine::PAYMENT_SUCCESS,
                    'fan_id' => $fan->id,
                    'user_id' => $user->id
                ];

                $this->fanUserRepository->updateOrCreate(
                    [
                        'fan_id' => $fan->id,
                        'user_id' => $user->id
                    ],
                    $paramSyncFan
                );

                dispatch(new SendMailUserJoinFan($user->email, [
                    'email' => $user->name ? $user->name : $user->email,
                    'fan_name' => $fan->title,
                    'plan_name' => $plan->title,
                ]));

                // Send notify to creator
                if ($this->userService->checkUserNotifyStatus($fan->author_id)) {
                    $notifyParams = [
                        'content' => $user->name ? $user->name : $user->email . ' ファン登録しました',
                        'type' => CommonDefine::FOLLOW,
                        'user_id' => $fan->author_id,
                        'fan_id' => $fan->id,
                        'created_by' => $user->id
                    ];

                    $this->notificationHistoryRepository->create($notifyParams);
                }
            }

            return ResponseHelper::ok();
        } catch (\Exception $ex) {
            return ResponseHelper::ok();
        }
    }

    public function userRegisterProduct(Request $request)
    {
        DB::beginTransaction();
        try {
            // Check payment key
            $paymentKey = $this->paymentKeyRepository->getByUser($request->token);
            if (!$paymentKey) {
                return ResponseHelper::bad([], 'key incorrect');
            }

            // Get detail product
            $product = $this->productRepository
                ->getByConditions(
                    [],
                    ['id' => $request->product_id],
                    '',
                    [],
                    [],

                )
                ->first();

            if (!$product) {
                return ResponseHelper::bad();
            }

            // Update product payment
            $paramSyncProduct = [
                'status' => CommonDefine::PAYMENT_SUCCESS,
                'payment_price' => $product->price,
                'payment_date' => Carbon::now(),
                'product_id' => $request->get('product_id'),
                'user_id' => auth()->user()->id
            ];

            $this->productPaymentRepository->updateOrCreate(
                [
                    'status' => CommonDefine::PAYMENT_CANCELLED,
                    'product_id' => $request->get('product_id'),
                    'user_id' => auth()->user()->id
                ],
                $paramSyncProduct
            );

            // Send notify to creator
            if ($this->userService->checkUserNotifyStatus($product->author_id)) {
                $notifyParams = [
                    'content' => auth()->user()->name ? auth()->user()->name : auth()->user()->email . ' ファン登録しました',
                    'type' => CommonDefine::FOLLOW,
                    'user_id' => $product->author_id,
                    'product_id' => $product->id,
                    'created_by' => auth()->user()->id
                ];

                $this->notificationHistoryRepository->create($notifyParams);
            }
            DB::commit();

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
}
