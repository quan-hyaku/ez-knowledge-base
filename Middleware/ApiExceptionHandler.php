<?php

namespace Packages\EzKnowledgeBase\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Packages\EzKnowledgeBase\Helpers\ApiErrorResponse;
use Throwable;

class ApiExceptionHandler
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ModelNotFoundException $e) {
            return ApiErrorResponse::make(
                'The requested resource was not found.',
                'not_found',
                404
            );
        } catch (ValidationException $e) {
            return ApiErrorResponse::make(
                'The given data was invalid.',
                'validation_error',
                422,
                $e->errors()
            );
        } catch (Throwable $e) {
            $message = config('app.debug')
                ? $e->getMessage()
                : 'An internal server error occurred.';

            return ApiErrorResponse::make(
                $message,
                'server_error',
                500
            );
        }
    }
}
