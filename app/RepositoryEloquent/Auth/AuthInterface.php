<?php


namespace App\RepositoryEloquent\Auth;

interface AuthInterface
{
    public function login($request);

    public function register($request);

    public function registerTemp($request);

    public function resetPassword($request);

    public function switchUser($request);

    /**
     * @param $pass
     * @return bool
     */
    public function changePassword($pass): bool;

    public function updateIsNotification($params);

    public function sendMailListUser();
}
