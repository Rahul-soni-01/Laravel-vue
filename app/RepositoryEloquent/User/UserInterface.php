<?php


namespace App\RepositoryEloquent\User;

use Illuminate\Support\Collection;

interface UserInterface
{
    public function getList($request);

    public function getListByUser($request);

    /**
     * @param $request
     * @return mixed
    */
    public function changeStatusUser($request);

    /**
     * @param $request
     * @return mixed
     */
    public function changeConfirmStatus($request);

    /**
     * @param $id
     * @return mixed
     */
    public function deleteUser($id);

    /**
     * @param $id
     * @return mixed
     */
    public function getDetail($id);

    /**
     * @param $request
     * @return array
     */
    public function getUserFollowFan($request) : array;
}
