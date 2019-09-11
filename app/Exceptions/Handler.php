<?php

namespace App\Exceptions;

use Exception;
use App\Http\Response\ResourceResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Traits\ExceptionCustomHandler;

class Handler extends ExceptionHandler
{
    use ExceptionCustomHandler;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $response = $this->handle($request,$exception);
        if ($response) {
            return $response;
        }
        return parent::render($request, $exception);
    }

    public function handle($request,$exception)
    {
        $responseJson = $this->custom_handle($exception);
        if($responseJson)
        {
            if ($request->ajax())
            {
                return app(ResourceResponse::class)->code($responseJson['code'])->status($responseJson['status'])->message($responseJson['message'])->json();
                //return app(ApiResponse::class)->json($responseJson);
            }else{
                return response()->view('message.error',$responseJson);
            }
        }
    }
}
