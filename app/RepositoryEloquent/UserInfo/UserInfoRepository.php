<?php
namespace App\RepositoryEloquent\UserInfo;

use App\Define\CommonDefine;
use App\Models\UserInfo;
use App\RepositoryEloquent\BaseRepository;
use phpDocumentor\Reflection\Types\Collection;

class UserInfoRepository extends BaseRepository implements UserInfoInterface
{
    public function model()
    {
        return UserInfo::class;
    }

    /**
     * @param $request
     * return array
     */
    public function updateUserInfo($params) : mixed
    {
        $userInfo = $this->updateOrCreate(
            [
                'user_id' => $params['user_id']
            ],
            $params
        );

        return $userInfo;
    }
}
