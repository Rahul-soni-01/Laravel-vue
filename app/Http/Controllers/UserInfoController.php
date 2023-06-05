<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use App\Models\File;
use App\Models\UserInfo;
use App\RepositoryEloquent\File\FileInterface;
use Illuminate\Http\Request;
use App\RepositoryEloquent\UserInfo\UserInfoInterface;
use App\RepositoryEloquent\Auth\AuthInterface;
use App\Http\Requests\UserInfo\UpdateRequest;
use App\Http\Requests\UserInfo\UserUpdateInfoRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\FileService;
use PHPUnit\Exception;

class UserInfoController extends Controller
{
    /**
     * @var UserInfoInterface $userInfoRepository
     */
    private  UserInfoInterface $userInfoRepository;

    /**
     * @var AuthInterface $userRepository
     */
    private  AuthInterface $userRepository;

    /**
     * @var FileInterface $fileRepository
     */
    private  FileInterface $fileRepository;

    /**
     * @var FileService $service
     */
    private  FileService $service;

    /**
     * @param UserInfoInterface $userInfoRepository
     * @param AuthInterface $userRepository
     * @param FileInterface $fileRepository
     */
    public function __construct(
        UserInfoInterface $userInfoRepository,
        AuthInterface $userRepository,
        FileInterface $fileRepository,
        FileService $service
    ) {
        $this->userInfoRepository = $userInfoRepository;
        $this->userRepository = $userRepository;
        $this->fileRepository = $fileRepository;
        $this->service = $service;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function request(UpdateRequest $request)
    {
        $params = $request->only([
            'id',
            'user_id',
            'full_name',
            'sex',
            'address',
            'language',
            'note',
            'phone'
        ]);
        $params['birth_day'] = Carbon::parse($request->birth_day)->format('Y/m/d');

        if ($request->has('avatar') && $request->avatar) {
            $path = 'avatar';
            $urlPath = $this->service->storeFileToS3($request->avatar, $path);
            $params['avt_url'] = $urlPath;
        }

        if ($request->has('front_photo') && $request->front_photo) {
            $path = 'user_image';
            $urlPathFrontPhoto = $this->service->storeFileToS3($request->front_photo, $path);
            $params['front_photo'] = $urlPathFrontPhoto;
        }

        if ($request->has('backside_photo') && $request->backside_photo) {
            $path = 'user_image';
            $urlPathBacksidePhoto = $this->service->storeFileToS3($request->backside_photo, $path);
            $params['backside_photo'] = $urlPathBacksidePhoto;
        }

        $userInfo = $this->userInfoRepository->updateUserInfo($params);

        if ($userInfo) {
            $user = $this->userRepository->findOrFail($request->user_id);
            $user->name = $request->full_name;
            $user->confirm_status = CommonDefine::UNDER_REVIEW;
            $user->save();

            return ResponseHelper::ok();
        }

        return ResponseHelper::bad();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserInfo  $userInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserInfo $userInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function userUpdateInfo(UserUpdateInfoRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->only([
                'avatar',
                'email',
                'phone',
                'full_name',
                'language',
                'birth_day',
                'sex',
                'note',
                'category_favorite',
            ]);
            $params['user_id'] = auth()->user()->id;

            if ($request->has('avatar') && $request->avatar) {
                $path = 'avatar';
                $urlPath = $this->service->storeFileToS3($request->avatar, $path);
                $params['avt_url'] = $urlPath;
            }

            $params['birth_day'] = Carbon::parse($request->birth_day)->format('Y/m/d');

            $userInfo = $this->userInfoRepository->updateUserInfo($params);

            if (!$userInfo) {
                return ResponseHelper::bad([], 'cannot update info');
            }

            $user = $this->userRepository->findOrFail($params['user_id']);
            $user->name = $request->full_name;
            $user->save();
            DB::commit();

            return ResponseHelper::ok();
        } catch (Exception $ex) {
            DB::rollBack();

            throw $ex;
        }
    }

    public function userUpdateNotification(Request $request)
    {
        try {
            if (isset($request->is_notification)) {
                $this->userRepository->updateIsNotification(
                    $request->is_notification
                );

                return ResponseHelper::ok();
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
