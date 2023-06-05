<?php

namespace App\Define;

use Illuminate\Support\Collection;

class CommonDefine
{
    const TOKEN_CODE_LENGTH = 200;

    //Default limit
    const DEFAULT_LIMIT = 10;
    const DEFAULT_LIMIT_FAVORITE_PAGE = 16;

    const LANG_TYPE_EN = 'en';
    const LANG_TYPE_JA = 'ja';
    const LANG_TYPE = [
        self::LANG_TYPE_JA,
        self::LANG_TYPE_EN,
    ];

    const ACTIVE = 1;
    const NOT_ACTIVE = 0;

    // status user
    const USER_ACTIVE = 1;
    const USER_NOT_ACTIVE = 0;
    const USER_IS_ADMIN = 1;
    const MINE_TOKEN_EXPIRE = 1440;
    const STATUS_USER = [
        self::USER_ACTIVE => '有効',
        self::USER_NOT_ACTIVE => '無効',
    ];

    // Status user info confirm
    const UN_AUTHENTICATED = 0;
    const UNDER_REVIEW = 1;
    const AUTHENTICATED = 2;
    const STATUS_CONFIRM_USER_INFO = [
        self::UN_AUTHENTICATED => '未認証',
        self::UNDER_REVIEW => '審査中',
        self::AUTHENTICATED => '認証済'
    ];

    const POST_STATUS_ENABLE = 1;
    const POST_STATUS_DISABLE = 0;

    const POST_IS_PUBLIC = 1;
    const POST_NOT_PUBLIC = 0;

    const PRODUCT_IS_PUBLIC = 1;
    const PRODUCT_NOT_PUBLIC = 0;

    const USER_IS_NOTIFICATION = 1;
    const USER_NOT_NOTIFICATION = 0;

    //Define Brand
    const BRAND_MAN = '総合(男性向け)';
    // const BRAND_WOMEN = '女性向け';
    const BRAND_ALL = '全年齢(成人向け投稿不可)';
    const BRAND = [
        self::BRAND_MAN,
        // self::BRAND_WOMEN,
        self::BRAND_ALL,
    ];

    //Status Notification
    const IS_READ = 1;
    const UN_READ = 0;

    //Notification Type
    const LIKE = 1;
    const FOLLOW = 2;
    const COMMENT = 3;
    const NEW_PRODUCT_OR_POST = 4;

    //Message File Type
    const IMAGE = 1;
    const VIDEO = 2;

    //Product type
    const PRODUCT_VIDEO = 2;
    const PRODUCT_IMAGE = 1;

    //Payment Status
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_CANCELLED = 0;

    //Fanclub status
    const FAN_PUBLIC = 1;
    const FAN_PRIVATE = 0;

    //Type Payment In Plan

    const PAYMENT_MONTH = 1;
    const PAYMENT_YEAR = 2;

    //Payment key status
    const PAYMENT_KEY_ACTIVE = 1;
    const PAYMENT_KEY_DEACTIVE = 0;
}
