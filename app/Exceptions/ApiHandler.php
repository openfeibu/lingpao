<?php

namespace App\Exceptions;

use Exception;
use App\Http\Response\ApiResponse;
use Dingo\Api\Exception\Handler as DingoHandler;
use App\Traits\ExceptionCustomHandler;

class ApiHandler extends DingoHandler
{
    use ExceptionCustomHandler;

    public function handle(Exception $exception)
    {
        $responseJson = $this->custom_handle($exception);
        if($responseJson)
        {
            return app(ApiResponse::class)->code($responseJson['code'])->message($responseJson['message'])->json();
        }
        return parent::handle($exception);
    }

}