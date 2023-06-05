<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Fan\RegisterFanRequest;
use App\RepositoryEloquent\FanFavorite\FanFavoriteInterface;
use App\RepositoryEloquent\FanUser\FanUserInterface;
use App\RepositoryEloquent\Plan\PlanInterface;
use App\RepositoryEloquent\Plan\PlanRepository;
use App\RepositoryEloquent\PlanUser\PlanUserInterface;
use App\RepositoryEloquent\User\UserInterface;
use App\Services\FanService;
use Illuminate\Http\Request;
use App\Services\FileService;
use App\RepositoryEloquent\Fan\FanInterface;
use App\Http\Requests\Fan\CreateRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FanController extends Controller
{
    /**
     * @var FileService;
     */
    private FileService $fileService;

    /**
     * @var FanInterface;
     */
    private FanInterface $fanRepository;

    /**
     * @var FanUserInterface;
     */
    private FanUserInterface $fanUserRepository;

    /**
     * @var PlanUserInterface;
     */
    private PlanUserInterface $planUserRepository;

    /**
     * @var PlanInterface;
     */
    private PlanInterface $planRepository;

    /**
     * @var UserInterface;
     */
    private UserInterface $userRepository;

    /**
     * @var FanFavoriteInterface
     */
    private FanFavoriteInterface $fanFavoriteRepository;

    /**
     * @var FanService
     */
    private FanService $fanService;

    /**
     * @param  FileService $fileService
     * @param  FanInterface $fanRepository
     * @param  FanUserInterface $fanUserRepository
     * @param  PlanUserInterface $planUserRepository
     * @param  PlanInterface $planRepository
     * @param UserInterface $userRepository
     */
    public function __construct(
        FileService $fileService,
        FanInterface $fanRepository,
        FanUserInterface $fanUserRepository,
        PlanUserInterface $planUserRepository,
        PlanInterface $planRepository,
        UserInterface $userRepository,
        FanFavoriteInterface $fanFavoriteRepository,
        FanService $fanService
    ) {
        $this->fileService = $fileService;
        $this->fanRepository = $fanRepository;
        $this->fanUserRepository = $fanUserRepository;
        $this->planUserRepository = $planUserRepository;
        $this->planRepository = $planRepository;
        $this->userRepository = $userRepository;
        $this->fanFavoriteRepository = $fanFavoriteRepository;
        $this->fanService = $fanService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    protected function submitFormError($field, $message)
    {
        $error = [$field => [$message]];
        return new Response([
            'errors' => $error,
            'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Create a new fan support.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request)
    {
        if (!$request->id) {
            if (!isset($request->fileCover) || !isset($request->fileAvatar)) {
                return $this->submitFormError("name", __("3つ以上の画像をアップロードしてください"));
            }
        }
        $params = $request->only([
            'title',
            'sub_title',
            'nickname',
            'category_id',
            'brand_id'
        ]);
        $params['content'] =  $request->get('content', '');
        $params['status'] =  $request->get('status', '');
        $params['author_id'] = auth()->user()->id;

        if ($request->has('fileCover') && $request->fileCover) {
            $urlPhoto = $this->fileService->storeFileToS3($request->fileCover, 'fan');
            $params['photo'] = $urlPhoto;
        }

        if ($request->has('fileAvatar') && $request->fileAvatar) {
            $urlAvatar = $this->fileService->storeFileToS3($request->fileAvatar, 'fan');
            $params['avt'] = $urlAvatar;
        }

        // if ($request->has('fileBackground') && $request->fileBackground) {
        //     $urlBackground = $this->fileService->storeFileToS3($request->fileBackground, 'fan');
        //     $params['background'] = $urlBackground;
        // }

        $create = $this->fanRepository->createFan($params);

        if (!$create) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Remove a fan support.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->fanRepository->destroy($id);

        return ResponseHelper::ok();
    }

    /**
     * @param Request $request
     * @return Response;
     */
    public function userListFanClub(Request $request)
    {
        $request['public'] = 1;

        return ResponseHelper::ok($this->fanRepository->getList($request));
    }

    /**
     * @param Request $request
     * @return Response;
     */
    public function creatorListFanClub(Request $request)
    {
        return ResponseHelper::ok($this->fanRepository->getList($request));
    }

    /**
     * @param Request $request
     * @return Response;
     */
    public function userRegisterFanClub(RegisterFanRequest $request)
    {
        DB::beginTransaction();
        try {
            $userPay = Arr::get($request, 'price_pay');
            $fan = $this->fanRepository->findOrFail($request->fan_id);

            $paramSync = [
                auth()->user()->id => [
                    'price_pay' => $userPay,
                    'month' => $request->month,
                    'payment_date' => Carbon::now()
                ]
            ];

            $fan->users()->sync($paramSync);
            DB::commit();

            return ResponseHelper::ok();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * Get detail a fan club for user
     *
     * @param Request $request
     * @return Response
     */
    public function userDetailFan($id)
    {
        return ResponseHelper::ok($this->fanRepository->userDetailFan($id));
    }

    public function getFromNickname($nickname)
    {
        return ResponseHelper::ok($this->fanRepository->getFromNickname($nickname));
    }

    /**
     * Get detail a fan club for creator
     *
     * @param Request $request
     * @return Response
     */
    public function creatorDetailFan($id)
    {
        return ResponseHelper::ok($this->fanRepository->creatorDetailFan($id));
    }

    /**
     * Get detail a fan club for creator
     *
     * @return Response
     */
    public function getByAuthor()
    {
        return ResponseHelper::ok($this->fanRepository->getByAuthor(auth()->user()->id));
    }

    /**
     * Get detail a fan club for creator
     * @param Request $request
     * @return Response
     */
    public function deleteUserFollow(Request $request)
    {
        try {
            DB::beginTransaction();
            $fanId = $request->get('fan_id');
            $userId = $request->get('user_id');

            $userFollow = $this->fanUserRepository->findCondition(
                [
                    'fan_id' => $fanId,
                    'user_id' => $userId
                ]
            );
            if (!$userFollow) {
                return ResponseHelper::bad();
            }
            $this->fanUserRepository->destroy($userFollow->id);

            $listPlan = $this->planRepository->getByConditions(
                [],
                [
                    'fan_id' => $fanId,
                ]
            );

            if (empty($listPlan)) {
                return ResponseHelper::bad();
            }

            foreach ($listPlan as $plan) {
                $userFollowPlan = $this->planUserRepository->findCondition(
                    [
                        'plan_id' => $plan->id,
                        'user_id' => $userId
                    ]
                );

                $id = $userFollowPlan->id ?? null;
                if ($id) {
                    $this->planUserRepository->destroy($id);
                }
            }
            DB::commit();

            return ResponseHelper::ok();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        } //end try
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getUserFollowFan(Request $request)
    {
        return ResponseHelper::ok($this->userRepository->getUserFollowFan($request));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getFavoriteFans(Request $request)
    {
        return ResponseHelper::ok($this->fanRepository->getFavoriteFans($request));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function favoriteFan(Request $request)
    {
        return ResponseHelper::ok($this->fanService->favoriteFan($request));
    }
}
