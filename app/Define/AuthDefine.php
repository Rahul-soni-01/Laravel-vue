<?php

namespace App\Define;

/**
 * Category define class
 * Class CategoryDefine
 * @package App\Define\Models
 */
class AuthDefine
{
    const OAUTH_TOKEN_URI = '/oauth/token';

    // Expired time day
    const TOKEN_EXPIRE_DAY = 30;
    const STATUS_TOKEN_EXPIRE = 410;


    const ROLE_USER = 1;
    const ROLE_CREATE = 2;
    const ROLE_ADMIN = 3;
}
