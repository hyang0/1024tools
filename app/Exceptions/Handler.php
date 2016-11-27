<?php

namespace App\Exceptions;

use Exception;
use App\Models\Typo;
use App\Support\ApiResponse;
use App\Exceptions\Exception as ToolsException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof NotFoundHttpException) {
            if ($route = Typo::getRoute($request->path())) {
                return redirect()->route($route);
            }
        }

        if ($e instanceof HttpException) {
            return parent::render($request, $e);
        }

        if ($e instanceof ToolsException) {
            if ($request->ajax()) {
                return ApiResponse::error($e->getMessage()); 
            }
        }

        return response()->make(view('errors.error'), 500);

    }
}
