<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\RepositoryEloquent\Auth\AuthInterface;
use App\Http\Requests\Auth\RegisterTempRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\RepositoryEloquent\PasswordReset\PasswordResetInterface;
use App\Jobs\SendMailResetPassword;

class AuthController extends Controller
{
    /**
     * @var AuthInterface
     */
    public $authRepository;

    /**
     * @var PasswordResetInterface
     */
    private $passwordResetRepository;

    public function __construct(
        AuthInterface $authRepository,
        PasswordResetInterface $passwordResetRepository
    ) {
        $this->authRepository = $authRepository;
        $this->guard = "api";
        $this->passwordResetRepository = $passwordResetRepository;
    }


    /**
     * Login
     *
     * @param LoginRequest $request
     * @return \Exception|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        try {
            $login = $this->authRepository->login($request);
            if (!$login['success'] && $login['code'] == 400) {
                return ResponseHelper::bad([], $login['message'], $login['code']);
            }

            if (!$login['success'] && $login['code'] == 410) {
                return ResponseHelper::expired($login['message'], $login['code']);
            }
            return ResponseHelper::ok($login['data'], $login['message']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     *  forgot Password
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws Exception
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $resetPassword = $this->authRepository->getUserResetPassword($request);
            if (!$resetPassword) {
                return ResponseHelper::bad([], 'システムにはユーザーが存在しません。');
            }

            $createReset = $this->passwordResetRepository->createResetPassword($resetPassword);

            if ($createReset) {
                $this->dispatch(new SendMailResetPassword($createReset->email, $resetPassword));
                return ResponseHelper::ok();
            }

            return ResponseHelper::ok();
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * register account
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     * @throws \Exception
     */
    public function register(RegisterRequest $request)
    {
        try {
            $register = $this->authRepository->register($request);
            if (!$register['success'] && $register['code'] == 400) {
                return ResponseHelper::bad([], $register['message']);
            } elseif (!$register['success'] && $register['code'] == 410) {
                return ResponseHelper::expired($register['message']);
            } elseif (!$register['success']) {
                return ResponseHelper::error($register['message'], $request['code']);
            }

            return ResponseHelper::ok('', $register['message']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * registerTemp
     *
     * @param RegisterTempRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws Exception
     */
    public function registerTemp(RegisterTempRequest $request)
    {
        try {
            $register = $this->authRepository->registerTemp($request);
            if (!$register['success']) {
                return ResponseHelper::bad([], $register['message']);
            }
            return ResponseHelper::ok('', $register['message']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Update password
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updatePassword(RegisterRequest $request)
    {
        $user = $this->passwordResetRepository->findUserReset($request);
        if (!$user) {
            return ResponseHelper::bad([], 'システムにはユーザーが存在しません。');
        }
        $now = Carbon::now();
        if ($now->diffInSeconds($user->created_at) > 86400) {
            return ResponseHelper::expired('Link exprired');
        }
        $this->authRepository->resetPassword($request);

        return ResponseHelper::ok('', 'Password updated');
    }

    /**
     * logout
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout()
    {
        JWTAuth::parseToken()->invalidate(true);

        return ResponseHelper::ok();
    }

    /**
     * @param
     * @return Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $oldPassword = $request->get('old_password');
        $newPassword = $request->get('new_password');
        if (!Hash::check($oldPassword, auth()->user()->getAuthPassword())) {
            return ResponseHelper::dataNotFound('古いパスワードが正しくありません');
        }

        $changePass = $this->authRepository->changePassword($newPassword);

        if (!$changePass) {
            return ResponseHelper::bad([], '失敗');
        }

        return ResponseHelper::ok();
    }


    public function fakeSendmail()
    {
        try {
            $this->authRepository->sendMailListUser();

            return ResponseHelper::ok();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    public function userSupport(Request $request) {

        echo "Hi";
        echo "<br>====>".$request->input('email');
        echo "<br>====>".$request->input('dateTime');
        echo "<pre>";die;
        /* $request->validate([
            'image' => 'required|mimes:pdf,xlx,csv,jpg,jpeg,mp4,mov|max:2048',
        ]); */
        //echo "<pre>"; print_r($request->all());die;
    }
    public function userSupport2(Request $request) {
        echo "raj";
 echo "Hi";
        echo "<br>====>".$request->input('email');
        echo "<br>====>".$request->input('dateTime');
        echo "<pre>";die;
    }
}
