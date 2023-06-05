<?php

namespace App\RepositoryEloquent\Auth;

use App\Define\AuthDefine;
use App\Define\CommonDefine;
use App\Models\User;
use App\RepositoryEloquent\BaseRepository;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendMailRegisterTempAccount;
use App\Jobs\SendMailRegisterSuccess;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use App\Mail\RegisterMail;
use Illuminate\Support\Facades\Mail;

class AuthRepository extends BaseRepository implements AuthInterface
{
    public function model()
    {
        return User::class;
    }

    /**
     * @param $request
     * @return array
     */
    public function login($request)
    {
        $userFind = $this->model
            ->where('email', $request->get('email'))->with(['userInfo']);

        if ($request->get('is_admin') == CommonDefine::USER_IS_ADMIN) {
            $userFind = $userFind->where('role_id', AuthDefine::ROLE_ADMIN);
        } else {
            $userFind = $userFind->whereIn('role_id', [AuthDefine::ROLE_USER, AuthDefine::ROLE_CREATE]);
        }

        $userFind = $userFind->first();

        if (!$userFind) {
            return [
                'code' => 400,
                'success' => false,
                'message' => 'ログインに失敗しました。'
            ];
        }

        if ($userFind->status != CommonDefine::USER_ACTIVE) {
            return [
                'code' => 400,
                'success' => false,
                'message' => 'アカウントがロックされました。管理者に連絡してください。'
            ];
        }

        $token = JWTAuth::attempt(
            [
                'email' => $request->email,
                'password' => $request->password
            ]
        );

        if (!$token || !$userFind) {
            return [
                'code' => 400,
                'success' => false,
                'message' => 'ログインに失敗しました。'
            ];
        }

        $data = [
            'token' => $token,
            'email' => $userFind->email,
            'role_id' => $userFind->role_id
        ];

        if (isset($userFind->userInfo->language)) {
            $data['language'] = $userFind->userInfo->language;
        }

        return [
            'success' => true,
            'message' => 'Success',
            'data' => $data
        ];
    }

    /**
     * register account
     *
     * @param $request
     * @return array
     */
    public function register($request)
    {
        $register = [
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'register_token_verify' => null,
            'status' => CommonDefine::USER_ACTIVE,
            'role_id' => AuthDefine::ROLE_USER,
            'confirm_status' => CommonDefine::UN_AUTHENTICATED
        ];

        $userFind = $this->findCondition(
            [
                'email' => $request->get('email'),
                'register_token_verify' => $request->get('token'),
                'status' => CommonDefine::USER_NOT_ACTIVE
            ]
        );

        if (!$userFind) {
            return [
                'success' => false,
                'code' => 404,
                'message' => 'Not found user temp'
            ];
        }

        $now = Carbon::now();
        $diff = $now->diffInMinutes($userFind->updated_at);
        if ($diff > CommonDefine::MINE_TOKEN_EXPIRE) {
            return [
                'success' => false,
                'code' => 410,
                'message' => 'トークンの有効期限が切れています。',
            ];
        }

        $name = 'Username' . $userFind->id . rand(100000, 999999);
        $register['name'] = $name;

        $register = $this->updateByCondition(
            [
                'email' => $request->email,
                'status' => CommonDefine::USER_NOT_ACTIVE,
                'register_token_verify' => $request->get('token')
            ],
            $register
        );

        if (!$register) {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Register Fail',
            ];
        }

        dispatch(new SendMailRegisterSuccess($request->email, ['email' => $request->email]));

        return [
            'success' => true,
            'message' => "Register Success",
        ];
    }

    /**
     * register temp account
     *
     * @param $request
     * @return array
     */
    public function registerTemp($request)
    {
        try {
            DB::beginTransaction();
            $token = Str::random(CommonDefine::TOKEN_CODE_LENGTH);
            $userFind = $this->findCondition(
                [
                    'email' => $request->email,
                    'register_token_verify' => null
                ]
            );
            if ($userFind) {
                return [
                    'success' => false,
                    'message' => "このメールは既に存在します",
                ];
            }
            $userTempParam = [
                'email' => $request->email,
                'status' => CommonDefine::USER_NOT_ACTIVE,
                'register_token_verify' => $token
            ];
            $userTemp = $this->model->updateOrCreate(
                [
                    'email' => $request->email,
                    'status' => CommonDefine::USER_NOT_ACTIVE
                ],
                $userTempParam
            );
            if ($userTemp) {
                $send = dispatch(new SendMailRegisterTempAccount($userTemp->email, $userTempParam));

                if (!$send) {
                    return [
                        'success' => false,
                        'message' => "Mail not sent",
                    ];
                }
                DB::commit();
                return [
                    'success' => true,
                    'message' => "Mail sent to user",
                ];
            }

            return [
                'success' => false,
                'message' => "Register Fail",
            ];
        } catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * Reset Password
     *
     * @param $request
     * @return array
     */
    public function resetPassword($request)
    {
        $paramsUpdate = [
            'email' => $request->email,
            'status' => CommonDefine::USER_ACTIVE,
            'password' => Hash::make($request->password)
        ];

        try {
            $userUpdate = $this->updateByCondition(
                [
                    'email' => $request->email,
                    'status' => CommonDefine::USER_ACTIVE,
                ],
                $paramsUpdate
            );

            return $userUpdate;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @param $request
     * @return array|null
     */
    public function getUserResetPassword($request)
    {
        $user = $this->findCondition(
            [
                'email' => $request->email,
                'status' => CommonDefine::USER_ACTIVE
            ]
        );

        if (!$user) {
            return null;
        }
        $token = Str::random(CommonDefine::TOKEN_CODE_LENGTH);
        $params = [
            'email' => $request->email,
            'token' => $token
        ];

        return $params;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function switchUser($request)
    {
        $userRole = auth()->user();

        if ($userRole->confirm_status != CommonDefine::AUTHENTICATED) {
            return [
                'success' => false,
                'message' => '作成者ではなくユーザー'
            ];
        }

        $user = $this->update(
            [
                'role_id' => $request->get('role_id')
            ],
            auth()->user()->id
        );

        if (!$user) {
            return [
                'success' => false,
                'message' => '役割を切り替えることはできません'
            ];
        }

        return [
            'success' => true,
            'message' => '役割の切り替え'
        ];
    }

    /**
     * @param $pass
     * @return bool
     */
    public function changePassword($pass): bool
    {
        $change = $this->update(
            [
                'password' => Hash::make($pass)
            ],
            auth()->user()->id
        );

        return $change;
    }

    public function updateIsNotification($params)
    {
        $update = $this->update(
            [
                'is_notification' => (int)$params
            ],
            auth()->user()->id
        );

        return $update;
    }

    public function sendMailListUser()
    {
        try {
            $users = $this->model->where('name', '=', null)->get();

            foreach ($users as $user) {
                $this->updateByCondition(
                    [
                        'email' => $user->email,
                    ],
                    [
                        'name' => 'Username' . $user->id . rand(100000, 999999)
                    ]
                );
            }

            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
