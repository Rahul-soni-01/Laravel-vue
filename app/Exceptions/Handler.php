<?php

namespace App\Exceptions;

use App\Enums\ResponseCode;
use App\Helpers\FormatHelper;
use App\Helpers\ResponseHelper;
use App\Http\Resources\ResponseJson;
use App\Traits\HandleResponseApi;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Exceptions\AuthorizeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * @param $request
     * @param Throwable $exception
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        FormatHelper::logErrorMessage($exception);
        if ($exception instanceof ValidationException) {
            return ResponseHelper::validationError($exception->errors());
        } else if ($exception instanceof AuthenticationException) {
            return ResponseHelper::unauthorized();
        } else if ($exception instanceof ModelNotFoundException) {
            return ResponseHelper::dataNotFound();
        } else if ($exception instanceof NotFoundHttpException) {
            return ResponseHelper::resourceNotFound('404');
        } else if ($exception instanceof HttpException && $exception->getStatusCode() == 403) {
            return ResponseHelper::forbidden('403');
        }
        return ResponseHelper::error();
    }
}
