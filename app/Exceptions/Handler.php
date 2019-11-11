<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Log;

class Handler extends ExceptionHandler
{
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        $status = env('APP_STATUS_ERROR_TEXT');
        switch(get_class($exception)) {
            case ValidationException::class:
                return response()->json([
                    'status' => $status,
                    'error' => $exception->getMessage(),
                ], 400);
                break;
            case ModelNotFoundException::class:
                return response()->json([
                    'status' => $status,
                    'error' => 'not found',
                ], 404);
                break;
            case GeoException::class:
                return response()->json([
                    'status' => $status,
                    'error' => $exception->getMessage(),
                ], 422);
                break;
            case QueryException::class:
                $error = 'DB error';
            default:
                Log::error($exception);
                return response()->json([
                    'status' => $status,
                    'error' => $error ?? 'unknown error',
                    'time' => time(),
                ], env('APP_STATUS_ERROR_CODE'));
        }
    }
}
