<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ApiResponse;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);
            return $response;
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors(), $e->getMessage());
        } catch (AuthenticationException $e) {
            return ApiResponse::unauthorized($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return ApiResponse::notFound('The requested resource was not found');
        } catch (AccessDeniedHttpException $e) {
            return ApiResponse::forbidden($e->getMessage() ?: 'You do not have permission to access this resource');
        } catch (HttpException $e) {
            return ApiResponse::error($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = env('APP_DEBUG') ? $e->getMessage() : 'Server Error';
            
            return ApiResponse::error($message, $statusCode);
        }
    }
}
