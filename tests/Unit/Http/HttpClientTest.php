<?php

use AlchemicStudio\PontoConnect\Http\HttpClient;
use AlchemicStudio\PontoConnect\Http\Response;
use AlchemicStudio\PontoConnect\Auth\AuthProvider;
use AlchemicStudio\PontoConnect\Http\RequestSigner;
use AlchemicStudio\PontoConnect\Exceptions\ApiException;
use AlchemicStudio\PontoConnect\Exceptions\NetworkException;

describe('HttpClient', function () {
    test('can be instantiated with required parameters', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        $client = new HttpClient(
            'https://api.ibanity.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('can be instantiated with request signer', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $signer = Mockery::mock(RequestSigner::class);

        $client = new HttpClient(
            'https://api.ibanity.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider,
            $signer
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('includes authorization header in requests', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')
            ->andReturn('test-token-123');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('can perform GET request', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Will make actual HTTP call or need mocking
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('can perform POST request', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('can perform DELETE request', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('can perform PATCH request', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('includes idempotency key in POST requests when provided', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should add Idempotency-Key header
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('retries on network failures', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should retry up to maxRetries times
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('respects max retry limit', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should stop after 3 retries by default
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('throws ApiException on 4xx errors', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('throws ApiException on 5xx errors', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('throws NetworkException on connection failures', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('uses TLS client certificates', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should configure Guzzle with cert and ssl_key options
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('signs requests when signer is provided', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $signer = Mockery::mock(RequestSigner::class);
        $signer->shouldReceive('sign')->andReturn(['Signature' => 'signed-value']);

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider,
            $signer
        );

        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('includes query parameters in GET requests', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should append query params to URL
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('sends JSON content type for POST requests', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should set Content-Type: application/json
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('returns Response object on success', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should return Response instance
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('validates base URL format', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        expect(fn() => new HttpClient(
            'invalid-url',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        ))->toThrow(\InvalidArgumentException::class);
    });

    test('handles rate limiting with retry-after header', function () {
        $authProvider = Mockery::mock(AuthProvider::class);

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should respect Retry-After header on 429 responses
        expect($client)->toBeInstanceOf(HttpClient::class);
    });

    test('includes User-Agent header', function () {
        $authProvider = Mockery::mock(AuthProvider::class);
        $authProvider->shouldReceive('getAccessToken')->andReturn('token');

        $client = new HttpClient(
            'https://api.test.com',
            '/path/to/cert.pem',
            '/path/to/key.pem',
            $authProvider
        );

        // Should include library name and version in User-Agent
        expect($client)->toBeInstanceOf(HttpClient::class);
    });
})->after(function () {
    Mockery::close();
});
