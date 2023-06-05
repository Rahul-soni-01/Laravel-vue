<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\RepositoryEloquent\User\UserInterface;
use Illuminate\Http\Request;
use App\RepositoryEloquent\Auth\AuthInterface;
use App\Http\Requests\User\SwitchRoleRequest;
use App\Http\Requests\User\ListUserRequest;
use App\RepositoryEloquent\Fan\FanInterface;
use App\Jobs\SendMailDeleteAccount;
use App\Services\CommonService;
use App\RepositoryEloquent\PlanUser\PlanUserInterface;
use App\RepositoryEloquent\FanUser\FanUserInterface;
use App\Define\CommonDefine;

class UserController extends Controller
{
    /**
     * @var AuthInterface
     */
    private UserInterface $userRepository;

    private AuthInterface $authRepository;

    private FanInterface $fanRepository;

    /**
     * @var CommonService
     */
    private CommonService $commonService;

    private PlanUserInterface $planUserRepository;

    private FanUserInterface $fanUserRepository;

    /**
     * @param AuthInterface $userRepository
     */
    public function __construct(
        UserInterface $userRepository,
        AuthInterface $authRepository,
        FanInterface $fanRepository,
        CommonService $commonService,
        PlanUserInterface $planUserRepository,
        FanUserInterface $fanUserRepository
    ) {
        $this->userRepository = $userRepository;
        $this->authRepository = $authRepository;
        $this->fanRepository = $fanRepository;
        $this->commonService = $commonService;
        $this->planUserRepository = $planUserRepository;
        $this->fanUserRepository = $fanUserRepository;
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getCurrentUser()
    {
        $user = auth()->user();
        if ($user->status == 0) {
            return ResponseHelper::unauthorized('Account is locked');
        }
        $data = [
            'user' => $user->toArray(),
            'user_info' => $user->userInfo,
        ];
        return ResponseHelper::ok($data);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function switchRole(SwitchRoleRequest $request)
    {
        $user = $this->authRepository->switchUser($request);
        if (!$user['success']) {
            return ResponseHelper::bad([], $user['message']);
        }

        return ResponseHelper::ok();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getDataUser(ListUserRequest $request)
    {
        $data = $this->userRepository->getList($request);
        return ResponseHelper::ok($data);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function UserGetDataUser(ListUserRequest $request)
    {
        $data = $this->userRepository->getListByUser($request);
        return ResponseHelper::ok($data);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeStatusUser(Request $request)
    {
        $userUpdate = $this->userRepository->changeStatusUser($request);

        if (!$userUpdate) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeConfirmStatus(Request $request)
    {
        $userUpdate = $this->userRepository->changeConfirmStatus($request);

        if (!$userUpdate) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteUser(Request $request)
    {
        $userDestroy = $this->userRepository->deleteUser($request->get('user_id'));

        if (!$userDestroy['success']) {
            return  ResponseHelper::bad([], $userDestroy['message']);
        }

        return  ResponseHelper::ok();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getDetail(Request $request)
    {
        $userDetail = $this->userRepository->getDetail($request->get('user_id'));

        if (!$userDetail['success']) {
            return  ResponseHelper::bad([], $userDetail['message']);
        }

        return  ResponseHelper::ok($userDetail['data']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function creatorDeleteUser(Request $request)
    {
        $userDestroy = $this->userRepository->deleteUser($request->get('user_id'));

        if (!$userDestroy['success']) {
            return  ResponseHelper::bad([], $userDestroy['message']);
        }

        return  ResponseHelper::ok();
    }

    /**
     * Remove a comment.
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function deleteAccount($id)
    {
        $currentUser = auth()->user();

        $this->userRepository->destroy($id);

        $this->fanRepository->updateStatus($id);

        $planOfUser = $this->commonService->getPlanOfUser($currentUser->id);

        //update plan user
        $this->planUserRepository->updateByCondition(
            [
                'user_id' => $currentUser->id
            ],
            [
                'status' => CommonDefine::FAN_PRIVATE
            ]
        );

        $this->fanUserRepository->updateByCondition(
            [
                'user_id' => $currentUser->id
            ],
            [
                'status' => CommonDefine::FAN_PRIVATE
            ]
        );

        dispatch(new SendMailDeleteAccount($currentUser->email, [
            'email' => $currentUser->name ? $currentUser->name : $currentUser->email,
            'time_out' => date('Y-m-d H:i:s'),
            'login_id' => $currentUser->email,
            'info_plan' => $planOfUser
        ]));

        return  ResponseHelper::ok();
    }
}
