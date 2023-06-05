<?php

namespace App\Define;

class HttpResponseCode
{
    // Content status
    const SUCCESS = 200;
    const NO_CONTENT = 204;

    // 問い合わせ　重複
    const BAD_INQUIRY_DUPLICATE = 301;
    // 問い合わせ　上限
    const BAD_INQUIRY_MAXIMUM = 302;

    // Client errors status
    const BAD_REQUEST = 400;
    // ログイン後の認証エラー
    const UNAUTHORIZED = 401;
    const EMAIL_NOT_VERIFIED = 402;
    const USER_IS_SUSPENDED = 406;
    const UNAUTHORIZED_AFTER_LOGIN = 401;
    const FORBIDDEN = 403;
    const DATA_NOT_FOUND = 400;
    const RESOURCE_NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const VALIDATION_ERROR = 422;
    const REQUEST_TIMEOUT = 408;
    const CONFIRM_CODE_TIMEOUT = 410;
    // Server errors status
    const MAINTENANCE = 503;
    const PAYGENT_ERROR = 499;
    const SERVER_ERROR = 500;


}
