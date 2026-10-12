<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Converte qualquer exceção no formato de erro único da API:
 * { "message": string, "errors"?: { campo: [mensagens] } }
 */
class ApiErrorResponse
{
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! ($request->is('api/*') || $request->expectsJson())) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => self::make($e->getMessage(), 422, $e->errors()),
            $e instanceof AuthenticationException => self::make('Unauthenticated.', 401),
            $e instanceof AuthorizationException => self::make('This action is unauthorized.', 403),
            $e instanceof ModelNotFoundException => self::make('Resource not found.', 404),
            $e instanceof HttpExceptionInterface => self::make(self::httpMessage($e), $e->getStatusCode()),
            default => self::make(config('app.debug') ? $e->getMessage() : 'Server error.', 500),
        };
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private static function make(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json(
            array_filter(['message' => $message, 'errors' => $errors]),
            $status,
        );
    }

    private static function httpMessage(HttpExceptionInterface $e): string
    {
        return match ($e->getStatusCode()) {
            404 => 'Resource not found.',
            405 => 'Method not allowed.',
            429 => 'Too many requests.',
            default => $e->getMessage() ?: 'Request failed.',
        };
    }
}
