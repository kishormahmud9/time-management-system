<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * Register exception handling callbacks.
     */
    public function register(): void
    {
        //
    }

    /**
     * Render exception to JSON response (for API).
     */
    public function render($request, Throwable $exception)
    {
        // ✅ Validation error
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422);
        }

        // ✅ Unauthenticated / Invalid token
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login again.',
            ], 401);
        }

        // ✅ JWT Token invalid
        if ($exception instanceof TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token provided.',
            ], 401);
        }

        // ✅ JWT Token expired
        if ($exception instanceof TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired. Please login again.',
            ], 401);
        }

        // ✅ Route not found
        if ($exception instanceof RouteNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Requested route not found or not accessible.',
            ], 404);
        }

        // ✅ Generic internal error (development only)
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
