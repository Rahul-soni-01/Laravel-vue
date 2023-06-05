<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class ApiAuthMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $this->auth->parseToken()->authenticate();
            if (Auth::guard('api')->check()) {
                return $next($request);
            }
        } catch (JWTException $e) {
            if ($e instanceof TokenInvalidException) {
                return ResponseHelper::unauthorized('Token invalid');
            }
            if ($e instanceof TokenExpiredException) {
                return ResponseHelper::unauthorized('Token exprired');
            }

            return ResponseHelper::unauthorized('Unauthorized');
        }
    }
}
