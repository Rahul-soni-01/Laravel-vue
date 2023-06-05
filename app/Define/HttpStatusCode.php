<?php

namespace App\Define;

class HttpStatusCode
{
    // Content status
    const SUCCESS = 200;
    const NO_CONTENT = 204;

    // Client input errors status
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const VALIDATION_ERROR = 422;
    const REQUEST_TIMEOUT = 408;

    // Server errors status
    const SERVER_ERROR = 500;
    const MAINTENANCE = 503;
}
