<?php

use AlchemicStudio\PontoConnect\Exceptions\ApiException;
use AlchemicStudio\PontoConnect\Exceptions\PontoException;

describe('ApiException', function () {
    test('can be instantiated', function () {
        $exception = new ApiException('API error occurred');

        expect($exception)->toBeInstanceOf(ApiException::class)
            ->and($exception)->toBeInstanceOf(PontoException::class);
    });

    test('can be thrown and caught', function () {
        expect(fn() => throw new ApiException('API request failed'))
            ->toThrow(ApiException::class, 'API request failed');
    });

    test('handles 400 Bad Request scenario', function () {
        $exception = new ApiException('Bad Request: Invalid parameters', 400);

        expect($exception->getCode())->toBe(400)
            ->and($exception->getMessage())->toContain('Bad Request');
    });

    test('handles 404 Not Found scenario', function () {
        $exception = new ApiException('Resource not found', 404);

        expect($exception->getCode())->toBe(404)
            ->and($exception->getMessage())->toContain('not found');
    });

    test('handles 409 Conflict scenario', function () {
        $exception = new ApiException('Conflict: Idempotency key already used', 409);

        expect($exception->getCode())->toBe(409)
            ->and($exception->getMessage())->toContain('Conflict');
    });

    test('handles 422 Unprocessable Entity scenario', function () {
        $exception = new ApiException('Unprocessable Entity: Validation failed', 422);

        expect($exception->getCode())->toBe(422)
            ->and($exception->getMessage())->toContain('Validation failed');
    });

    test('handles 429 Too Many Requests scenario', function () {
        $exception = new ApiException('Too Many Requests: Rate limit exceeded', 429);

        expect($exception->getCode())->toBe(429)
            ->and($exception->getMessage())->toContain('Rate limit');
    });

    test('handles 500 Internal Server Error scenario', function () {
        $exception = new ApiException('Internal Server Error', 500);

        expect($exception->getCode())->toBe(500)
            ->and($exception->getMessage())->toContain('Internal Server Error');
    });

    test('handles 502 Bad Gateway scenario', function () {
        $exception = new ApiException('Bad Gateway: Upstream service unavailable', 502);

        expect($exception->getCode())->toBe(502)
            ->and($exception->getMessage())->toContain('Bad Gateway');
    });

    test('handles 503 Service Unavailable scenario', function () {
        $exception = new ApiException('Service Unavailable: Maintenance mode', 503);

        expect($exception->getCode())->toBe(503)
            ->and($exception->getMessage())->toContain('Service Unavailable');
    });

    test('handles account not found error', function () {
        $exception = new ApiException('Account with ID abc123 not found', 404);

        expect($exception->getMessage())->toContain('Account')
            ->and($exception->getMessage())->toContain('abc123');
    });

    test('handles payment already executed error', function () {
        $exception = new ApiException('Payment already executed', 409);

        expect($exception->getMessage())->toContain('Payment')
            ->and($exception->getMessage())->toContain('already executed');
    });

    test('handles insufficient funds error', function () {
        $exception = new ApiException('Insufficient funds for payment', 422);

        expect($exception->getMessage())->toContain('Insufficient funds');
    });

    test('handles API response with error details', function () {
        $errorDetails = json_encode([
            'errors' => [
                ['code' => 'invalidAmount', 'detail' => 'Amount must be positive'],
            ],
        ]);
        $exception = new ApiException("API error: {$errorDetails}", 422);

        expect($exception->getMessage())->toContain('invalidAmount')
            ->and($exception->getMessage())->toContain('Amount must be positive');
    });

    test('can be caught as PontoException', function () {
        expect(fn() => throw new ApiException('API error'))
            ->toThrow(PontoException::class);
    });

    test('can chain with previous exception', function () {
        $previous = new \Exception('Upstream error');
        $exception = new ApiException('API request failed', 500, $previous);

        expect($exception->getPrevious())->toBe($previous);
    });
});
