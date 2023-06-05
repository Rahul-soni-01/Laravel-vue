<?php

namespace App\RepositoryEloquent\UserInfo;

use phpDocumentor\Reflection\Types\Collection;

interface UserInfoInterface
{
    /**
    * @param $request
     * return array
     */
    public function updateUserInfo($params) : mixed;
}
