<?php

namespace App\Http\Middleware;

use App\Define\AuthDefine;
use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class IsCreator extends BaseMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function handle(Request $request, Closure $next)
    {

        
        $user = $this->auth->parseToken()->authenticate();
        // dd($user);
        if ($user && $user->role_id == AuthDefine::ROLE_CREATE)
        {
            return $next($request);
        }
        return ResponseHelper::forbidden();
    }
}
