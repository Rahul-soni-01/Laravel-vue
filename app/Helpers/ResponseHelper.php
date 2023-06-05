<?php

namespace App\Helpers;

use App\Define\HttpResponseCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ResponseHelper
{
    /**
     * 200ok
     * @param array|Collection $payload
     * @return ResponseFactory|Response
     */
    public static function ok($payload = [], $message = 'success')
    {
        return ResponseHelper::response(
            $message,
            HttpResponseCode::SUCCESS,
            $payload
        );
    }

    /**
     * response返却用の共通関数
     * @param string $message
     * @param int $responseCode
     * @param array|Collection $payload
     * @param int $statusCode
     * @return ResponseFactory|Response
     */
    public static function response(
        $messages = '',
        int $responseCode = 200,
        $payload = [],
        int $statusCode = 200
    ) {

        $makePayload = [
            "code" => $responseCode,
            "errors" => $messages,
        ];

        if ($responseCode == 200) {
            $makePayload['messages'] = $messages;
            unset($makePayload['errors']);
        }

        if ($payload && !Arr::get($payload, 'errors')) {
            $makePayload["data"] = $payload;
        } else if (Arr::get($payload, 'errors')) {
            $makePayload["errors"] = $payload['errors'];
        }

        $response = response($makePayload, $statusCode);

        return $response;
    }

    /**
     * DO SOMETHING
     * @param $list
     * @param LengthAwarePaginator $pager
     * @param array $custom
     * @return ResponseFactory|Response
     */
    public static function search($list, LengthAwarePaginator $pager, $custom = [])
    {
        $payload = array_merge([
            "list" => $list,
        ], $custom);

        $response = ResponseHelper::response(
            "success",
            HttpResponseCode::SUCCESS,
            $payload
        );

        $allItem = $pager->total();

        $first = ($pager->currentPage() - 1) * $pager->perPage();
        $until = $pager->currentPage() * $pager->perPage() - 1;
        if ($allItem > 0 && $allItem < $until) {
            $until = $allItem - 1;
        }

        $response->headers->add([
            "Content-Range" => "${first}-${until}/${allItem}",
        ]);

        return $response;
    }

    /**
     * ViewMore用のデータ返却結果
     * @param HttpResponseViewMore $viewMore
     * @param array $custom
     * @return ResponseFactory|Response
     */
    public static function viewMore(HttpResponseViewMore $viewMore, $custom = [])
    {
        $payload = array_merge([
            "list" => $viewMore->list,
        ], $custom);

        $response = ResponseHelper::response(
            "success",
            HttpResponseCode::SUCCESS,
            $payload
        );

        $response->headers->add([
            "X-Data-Length" => $viewMore->total,
        ]);

        return $response;
    }

    /**
     * Bad Request 400
     *
     * @param array $payload
     * @param string $message
     * @param int $responseCode
     *
     * @return ResponseFactory|Response
     */
    public static function bad(array $payload = [], $message = 'bad_request', int $responseCode = HttpResponseCode::BAD_REQUEST, \Exception $exception = null)
    {
        if ($exception) {
            $payload['errors'] = [
                'message' => $exception->getMessage(),
                'error_code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        return ResponseHelper::response(
            $message,
            $responseCode,
            $payload,
            400
        );
    }

    /**
     * 認証エラー 401
     * @return ResponseFactory|Response
     */
    public static function unauthorized()
    {
        return ResponseHelper::response(
            "unauthorized",
            HttpResponseCode::UNAUTHORIZED,
            [],
            401
        );
    }

    /**
     * ログイン後の認証エラー 401
     * @return ResponseFactory|Response
     */
    public static function unauthorizedAfterLogin()
    {
        return ResponseHelper::response(
            "unauthorized",
            HttpResponseCode::UNAUTHORIZED_AFTER_LOGIN,
            [],
            401
        );
    }

    /**
     * 権限なし 403
     * @param $responseCode
     * @return ResponseFactory|Response
     */
    public static function forbidden($responseCode = null, $payload = [])
    {
        if (!$responseCode) {
            $responseCode = HttpResponseCode::FORBIDDEN;
        }

        return ResponseHelper::response(
            "forbidden",
            $responseCode,
            $payload,
            403
        );
    }

    /**
     * 対象データなし 400
     *
     * @param int $httpResponseCode
     * @param string $message
     * @return ResponseFactory|Response
     */
    public static function dataNotFound($message = 'data_not_found', $httpResponseCode = HttpResponseCode::DATA_NOT_FOUND)
    {
        return ResponseHelper::response(
            $message,
            $httpResponseCode,
            [],
            400
        );
    }

    /**
     * 対象データなし 404
     *
     * @param int $httpResponseCode
     * @param string $message
     * @return ResponseFactory|Response
     */
    public static function resourceNotFound($payload = [], $message = 'Resource not found', $httpResponseCode = HttpResponseCode::RESOURCE_NOT_FOUND)
    {
        return ResponseHelper::response(
            $message,
            $httpResponseCode,
            $payload,
            404
        );
    }

    /**
     * バリデーションエラー
     * @param array $payload
     * @return ResponseFactory|Response
     */
    public static function validation_error(array $payload, $message = 'バリデーションエラー')
    {
        if ($errors = Arr::get($payload, 'errors')) {
            $errors = $errors->toArray();
            if (
                Arr::get($errors, 'email.0') == 'check_email_by_code'
                && Arr::get($errors, 'password.0') == 'check_pass_by_code'
            ) {
                $message = trans('api/user.change_email_confirm.email.check_email_pass_confirm');
            }
        }
        return ResponseHelper::response(
            $message,
            HttpResponseCode::VALIDATION_ERROR,
            $payload,
            422
        );
    }

    /**
     * セーバーエラー 500
     *
     * @param string $message
     * @param null $responseCode
     * @return ResponseFactory|Response
     */
    public static function error($message = 'Server Error', $responseCode = null, \Exception $exception = null)
    {
        if (!$responseCode) {
            $responseCode = HttpResponseCode::SERVER_ERROR;
        }
        if ($exception) {
            $payload['errors'] = [
                'message' => $exception->getMessage(),
                'error_code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];

            return ResponseHelper::response(
                $message,
                $responseCode,
                $payload,
                500
            );
        }
        return ResponseHelper::response(
            $message,
            $responseCode,
            [],
            500
        );
    }

    /**
     * Request expired - 400
     *
     * @param array $payload
     * @param string $message
     * @param int $responseCode
     *
     * @return ResponseFactory|Response
     */
    public static function expired($message = 'Expired', $responseCode = HttpResponseCode::CONFIRM_CODE_TIMEOUT)
    {
        return ResponseHelper::response(
            $message,
            $responseCode,
            [],
            400
        );
    }

    /**
     * Return response to hook action
     *
     * @param int $content {0: success,  1: error }
     *
     * @return ResponseFactory|Response
     */
    public static function hookResponse($content = 0)
    {
        return "result=$content";
    }

    public static function paygentError($payload = [], $message = 'Paygent error', $httpResponseCode = HttpResponseCode::PAYGENT_ERROR)
    {
        return ResponseHelper::response(
            $message,
            $httpResponseCode,
            $payload,
            499
        );
    }

    public static function validationError($message = 'Validation error', array $payload = [])
    {
        if ($errors = Arr::get($payload, 'errors')) {
            $errors = $errors->toArray();
            if (
                Arr::get($errors, 'email.0') == 'check_email_by_code'
                && Arr::get($errors, 'password.0') == 'check_pass_by_code'
            ) {
                $message = trans('api/user.change_email_confirm.email.check_email_pass_confirm');
            }
        }
        return ResponseHelper::response(
            $message,
            HttpResponseCode::VALIDATION_ERROR,
            $payload,
            422
        );
    }
}
