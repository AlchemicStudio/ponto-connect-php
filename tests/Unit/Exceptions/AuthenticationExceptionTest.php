<?php

use AlchemicStudio\PontoConnect\Exceptions\AuthenticationException;
use AlchemicStudio\PontoConnect\Exceptions\PontoException;

describe('AuthenticationException', function () {
    test('can be instantiated', function () {
        $exception = new AuthenticationException('Authentication failed');

        expect($exception)->toBeInstanceOf(AuthenticationException::class)
            ->and($exception)->toBeInstanceOf(PontoException::class)
            ->and($exception)->toBeInstanceOf(\Exception::class);
    });

    test('can be thrown and caught', function () {
        expect(fn () => throw new AuthenticationException('Invalid token'))
            ->toThrow(AuthenticationException::class, 'Invalid token');
    });

    test('can be caught as PontoException', function () {
        expect(fn () => throw new AuthenticationException('Auth error'))
            ->toThrow(PontoException::class);
    });

    test('stores authentication error message', function () {
        $message = 'OAuth2 token expired';
        $exception = new AuthenticationException($message);

        expect($exception->getMessage())->toBe($message);
    });

    test('stores HTTP status code for authentication errors', function () {
        $exception = new AuthenticationException('Unauthorized', 401);

        expect($exception->getCode())->toBe(401);
    });

    test('handles invalid credentials scenario', function () {
        $exception = new AuthenticationException('Invalid client credentials', 401);

        expect($exception->getMessage())->toBe('Invalid client credentials')
            ->and($exception->getCode())->toBe(401);
    });

    test('handles token refresh failure scenario', function () {
        $exception = new AuthenticationException('Refresh token expired', 401);

        expect($exception->getMessage())->toContain('Refresh token');
    });

    test('handles missing access token scenario', function () {
        $exception = new AuthenticationException('Access token not found');

        expect($exception->getMessage())->toContain('Access token');
    });

    test('can chain with previous exception', function () {
        $previous = new \Exception('Network timeout');
        $exception = new AuthenticationException('Token exchange failed', 0, $previous);

        expect($exception->getPrevious())->toBe($previous)
            ->and($exception->getPrevious()->getMessage())->toBe('Network timeout');
    });

    test('can be serialized to string', function () {
        $exception = new AuthenticationException('PKCE validation failed');
        $string = (string) $exception;

        expect($string)->toContain('PKCE validation failed')
            ->and($string)->toContain('AuthenticationException');
    });
});
