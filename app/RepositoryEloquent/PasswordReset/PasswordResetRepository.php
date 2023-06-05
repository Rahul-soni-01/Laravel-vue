<?php
namespace App\RepositoryEloquent\PasswordReset;

use App\Models\PasswordReset;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PasswordResetRepository extends BaseRepository implements PasswordResetInterface
{
    public function model()
    {
        return PasswordReset::class;
    }

    public function  createResetPassword($params)
    {
        $params['created_at'] = Carbon::now();
        $resetPass = $this->model->create($params);

        return $resetPass;
    }

    public function findUserReset($request)
    {
        $user = $this->findCondition(
            [
                'email' => $request->email,
                'token' => $request->token
            ],
        );

        return $user;
    }
}
