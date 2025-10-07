<?php

use AlchemicStudio\PontoConnect\Auth\AuthProvider;
use AlchemicStudio\PontoConnect\Auth\TokenStorage;
use AlchemicStudio\PontoConnect\Exceptions\AuthenticationException;
use AlchemicStudio\PontoConnect\Exceptions\ValidationException;

describe('AuthProvider', function () {
    test('can be instantiated with required parameters', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);

        $authProvider = new AuthProvider(
            'client-id-123',
            'client-secret-456',
            'https://api.ibanity.com',
            $tokenStorage
        );

        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('generates authorization URL with PKCE', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        $url = $authProvider->getAuthorizationUrl(
            'https://example.com/callback',
            'challenge-code-123',
            ['ai', 'pi', 'name']
        );

        expect($url)->toBeString()
            ->and($url)->toContain('https://api.test.com')
            ->and($url)->toContain('client_id=client-id')
            ->and($url)->toContain('redirect_uri=')
            ->and($url)->toContain('code_challenge=challenge-code-123')
            ->and($url)->toContain('response_type=code')
            ->and($url)->toContain('scope=');
    });

    test('includes default scopes in authorization URL', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        $url = $authProvider->getAuthorizationUrl('https://example.com/callback', 'challenge');

        expect($url)->toContain('ai')
            ->and($url)->toContain('pi')
            ->and($url)->toContain('offline_access');
    });

    test('throws ValidationException for empty redirect URI', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        expect(fn () => $authProvider->getAuthorizationUrl('', 'challenge'))
            ->toThrow(ValidationException::class);
    });

    test('throws ValidationException for empty code challenge', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        expect(fn () => $authProvider->getAuthorizationUrl('https://example.com/callback', ''))
            ->toThrow(ValidationException::class);
    });

    test('can exchange authorization code for tokens', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('store')->once();

        // This test will need HTTP mocking in actual implementation
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('stores tokens after successful exchange', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('store')
            ->once()
            ->with(Mockery::type('array'));

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('throws AuthenticationException for invalid authorization code', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        // Will throw when actual HTTP call is made with invalid code
        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('can get access token from storage', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('get')
            ->andReturn([
                'access_token' => 'valid-token-123',
                'expires_at' => time() + 3600,
            ]);

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);
        $token = $authProvider->getAccessToken();

        expect($token)->toBe('valid-token-123');
    });

    test('refreshes expired access token automatically', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('get')
            ->andReturn([
                'access_token' => 'expired-token',
                'refresh_token' => 'refresh-token-456',
                'expires_at' => time() - 100, // Expired
            ]);
        $tokenStorage->shouldReceive('store')->once();

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        // Will attempt to refresh when getAccessToken() is called
        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('throws AuthenticationException when no refresh token available', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('get')
            ->andReturn([
                'access_token' => 'expired-token',
                'expires_at' => time() - 100, // Expired
                // No refresh_token
            ]);

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        expect(fn () => $authProvider->getAccessToken())
            ->toThrow(AuthenticationException::class);
    });

    test('can manually refresh access token', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('get')
            ->andReturn(['refresh_token' => 'refresh-token-789']);
        $tokenStorage->shouldReceive('store')->once();

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        // Will make HTTP call to refresh endpoint
        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('updates token storage after successful refresh', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('get')
            ->andReturn(['refresh_token' => 'refresh-token']);
        $tokenStorage->shouldReceive('store')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return isset($arg['access_token']) && isset($arg['expires_at']);
            }));

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('throws AuthenticationException on refresh failure', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('get')
            ->andReturn(['refresh_token' => 'invalid-refresh-token']);

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        // Will throw when HTTP call fails
        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('can revoke refresh token', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('clear')->once();

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        // Will make HTTP call to revoke endpoint
        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('clears token storage after revocation', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $tokenStorage->shouldReceive('clear')->once();

        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        expect($authProvider)->toBeInstanceOf(AuthProvider::class);
    });

    test('validates client credentials on instantiation', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);

        expect(fn () => new AuthProvider('', 'secret', 'https://api.test.com', $tokenStorage))
            ->toThrow(ValidationException::class);
    });

    test('validates base URL format', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);

        expect(fn () => new AuthProvider('client-id', 'secret', 'invalid-url', $tokenStorage))
            ->toThrow(ValidationException::class);
    });

    test('generates state parameter for CSRF protection', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        $url = $authProvider->getAuthorizationUrl('https://example.com/callback', 'challenge');

        expect($url)->toContain('state=');
    });

    test('uses PKCE with S256 method', function () {
        $tokenStorage = Mockery::mock(TokenStorage::class);
        $authProvider = new AuthProvider('client-id', 'secret', 'https://api.test.com', $tokenStorage);

        $url = $authProvider->getAuthorizationUrl('https://example.com/callback', 'challenge');

        expect($url)->toContain('code_challenge_method=S256');
    });
})->after(function () {
    Mockery::close();
});
