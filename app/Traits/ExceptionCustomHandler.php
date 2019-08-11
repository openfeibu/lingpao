<?php
namespace App\Traits;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait ExceptionCustomHandler
{
    public function custom_handle($exception)
    {
        $responseJson = [];
        switch ($exception) {
            case ($exception instanceof \App\Exceptions\RequestSuccessException):
                $responseJson = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => $exception->getMessage() ? $exception->getMessage() : '请求成功',
                ];
                break;
            case ($exception instanceof \App\Exceptions\OutputServerMessageException):
                $responseJson = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ];
                break;
            case ($exception instanceof UnauthorizedHttpException):
                $responseJson = [
                    'code' => 401,
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ];
                break;
            case ($exception instanceof \App\Exceptions\UserUnauthorizedException):
                $responseJson = [
                    'code' => 401,
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ];
                break;
            case ($exception instanceof \App\Exceptions\Roles\PermissionDeniedException):
                $responseJson = [
                    'code' => 403,
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ];
                break;
            case ($exception instanceof \App\Exceptions\DataNotFoundException):
                $responseJson = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => $exception->getMessage() ? $exception->getMessage() : trans('error.404'),
                ];
                break;
            case ($exception instanceof \Illuminate\Session\TokenMismatchException):
                $responseJson = [
                    'code' => 419,
                    'status' => 'error',
                    'message' => '页面Token 失效，请重新进入',
                ];
                break;
            default:
                return false;
                break;
        }
        return $responseJson;
    }
}