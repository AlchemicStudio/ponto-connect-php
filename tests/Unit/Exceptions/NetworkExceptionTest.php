<?php

use AlchemicStudio\PontoConnect\Exceptions\NetworkException;
use AlchemicStudio\PontoConnect\Exceptions\PontoException;

describe('NetworkException', function () {
    test('can be instantiated', function () {
        $exception = new NetworkException('Network error occurred');

        expect($exception)->toBeInstanceOf(NetworkException::class)
            ->and($exception)->toBeInstanceOf(PontoException::class);
    });

    test('can be thrown and caught', function () {
        expect(fn() => throw new NetworkException('Connection failed'))
            ->toThrow(NetworkException::class, 'Connection failed');
    });

    test('handles connection timeout scenario', function () {
        $exception = new NetworkException('Connection timeout after 30 seconds');

        expect($exception->getMessage())->toContain('timeout')
            ->and($exception->getMessage())->toContain('30 seconds');
    });

    test('handles DNS resolution failure scenario', function () {
        $exception = new NetworkException('DNS resolution failed for api.ibanity.com');

        expect($exception->getMessage())->toContain('DNS')
            ->and($exception->getMessage())->toContain('api.ibanity.com');
    });

    test('handles SSL/TLS certificate error scenario', function () {
        $exception = new NetworkException('SSL certificate verification failed');

        expect($exception->getMessage())->toContain('SSL')
            ->and($exception->getMessage())->toContain('certificate');
    });

    test('handles connection refused scenario', function () {
        $exception = new NetworkException('Connection refused by server');

        expect($exception->getMessage())->toContain('Connection refused');
    });

    test('handles network unreachable scenario', function () {
        $exception = new NetworkException('Network is unreachable');

        expect($exception->getMessage())->toContain('unreachable');
    });

    test('handles read timeout scenario', function () {
        $exception = new NetworkException('Read timeout while waiting for response');

        expect($exception->getMessage())->toContain('Read timeout');
    });

    test('handles connection reset scenario', function () {
        $exception = new NetworkException('Connection reset by peer');

        expect($exception->getMessage())->toContain('reset');
    });

    test('handles too many redirects scenario', function () {
        $exception = new NetworkException('Too many redirects (maximum 10)');

        expect($exception->getMessage())->toContain('redirects');
    });

    test('handles proxy connection error scenario', function () {
        $exception = new NetworkException('Proxy connection failed');

        expect($exception->getMessage())->toContain('Proxy');
    });

    test('handles client certificate error scenario', function () {
        $exception = new NetworkException('Client certificate not found or invalid');

        expect($exception->getMessage())->toContain('Client certificate');
    });

    test('handles TLS handshake failure scenario', function () {
        $exception = new NetworkException('TLS handshake failed');

        expect($exception->getMessage())->toContain('TLS handshake');
    });

    test('handles socket error scenario', function () {
        $exception = new NetworkException('Socket error: broken pipe');

        expect($exception->getMessage())->toContain('Socket error');
    });

    test('can be caught as PontoException', function () {
        expect(fn() => throw new NetworkException('Network error'))
            ->toThrow(PontoException::class);
    });

    test('can chain with previous exception', function () {
        $previous = new \Exception('cURL error 28');
        $exception = new NetworkException('Operation timeout', 0, $previous);

        expect($exception->getPrevious())->toBe($previous)
            ->and($exception->getPrevious()->getMessage())->toBe('cURL error 28');
    });

    test('can include error code', function () {
        $exception = new NetworkException('Connection error', 28);

        expect($exception->getCode())->toBe(28);
    });
});
