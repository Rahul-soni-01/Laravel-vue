<?php

namespace App\RepositoryEloquent\PasswordReset;

interface PasswordResetInterface
{

    public function createResetPassword($params);

    public function findUserReset($request);

}

