<?php

use AlchemicStudio\PontoConnect\Auth\TokenStorage;

describe('TokenStorage', function () {
    test('can be instantiated', function () {
        $storage = new TokenStorage();

        expect($storage)->toBeInstanceOf(TokenStorage::class);
    });

    test('can store token data', function () {
        $storage = new TokenStorage();
        $tokenData = [
            'access_token' => 'token-123',
            'refresh_token' => 'refresh-456',
            'expires_in' => 3600,
        ];

        $storage->store($tokenData);

        expect($storage->get())->toBe($tokenData);
    });

    test('can retrieve stored token data', function () {
        $storage = new TokenStorage();
        $tokenData = ['access_token' => 'token-789'];

        $storage->store($tokenData);
        $retrieved = $storage->get();

        expect($retrieved['access_token'])->toBe('token-789');
    });

    test('returns null when no token stored', function () {
        $storage = new TokenStorage();

        expect($storage->get())->toBeNull();
    });

    test('can clear stored tokens', function () {
        $storage = new TokenStorage();
        $storage->store(['access_token' => 'token-123']);

        $storage->clear();

        expect($storage->get())->toBeNull();
    });

    test('can check if token exists', function () {
        $storage = new TokenStorage();

        expect($storage->has())->toBeFalse();

        $storage->store(['access_token' => 'token-123']);

        expect($storage->has())->toBeTrue();
    });

    test('stores expiration timestamp', function () {
        $storage = new TokenStorage();
        $expiresAt = time() + 3600;

        $storage->store([
            'access_token' => 'token-123',
            'expires_at' => $expiresAt,
        ]);

        $retrieved = $storage->get();

        expect($retrieved['expires_at'])->toBe($expiresAt);
    });

    test('can check if token is expired', function () {
        $storage = new TokenStorage();

        $storage->store([
            'access_token' => 'expired-token',
            'expires_at' => time() - 100, // Past expiry
        ]);

        expect($storage->isExpired())->toBeTrue();
    });

    test('can check if token is not expired', function () {
        $storage = new TokenStorage();

        $storage->store([
            'access_token' => 'valid-token',
            'expires_at' => time() + 3600, // Future expiry
        ]);

        expect($storage->isExpired())->toBeFalse();
    });

    test('returns true for expired when no token stored', function () {
        $storage = new TokenStorage();

        expect($storage->isExpired())->toBeTrue();
    });

    test('can get access token directly', function () {
        $storage = new TokenStorage();

        $storage->store(['access_token' => 'direct-token-123']);

        expect($storage->getAccessToken())->toBe('direct-token-123');
    });

    test('returns null when no access token available', function () {
        $storage = new TokenStorage();

        expect($storage->getAccessToken())->toBeNull();
    });

    test('can get refresh token directly', function () {
        $storage = new TokenStorage();

        $storage->store(['refresh_token' => 'refresh-token-456']);

        expect($storage->getRefreshToken())->toBe('refresh-token-456');
    });

    test('returns null when no refresh token available', function () {
        $storage = new TokenStorage();

        expect($storage->getRefreshToken())->toBeNull();
    });

    test('overwrites previous token data on store', function () {
        $storage = new TokenStorage();

        $storage->store(['access_token' => 'old-token']);
        $storage->store(['access_token' => 'new-token']);

        expect($storage->getAccessToken())->toBe('new-token');
    });

    test('preserves all token data fields', function () {
        $storage = new TokenStorage();
        $tokenData = [
            'access_token' => 'token-123',
            'refresh_token' => 'refresh-456',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'expires_at' => time() + 3600,
            'scope' => 'ai pi offline_access',
        ];

        $storage->store($tokenData);
        $retrieved = $storage->get();

        expect($retrieved['access_token'])->toBe('token-123')
            ->and($retrieved['refresh_token'])->toBe('refresh-456')
            ->and($retrieved['token_type'])->toBe('Bearer')
            ->and($retrieved['scope'])->toBe('ai pi offline_access');
    });

    test('can calculate expires_at from expires_in', function () {
        $storage = new TokenStorage();
        $beforeTime = time();

        $storage->store([
            'access_token' => 'token-123',
            'expires_in' => 3600,
        ]);

        $afterTime = time();
        $retrieved = $storage->get();

        expect($retrieved['expires_at'])->toBeGreaterThanOrEqual($beforeTime + 3600)
            ->and($retrieved['expires_at'])->toBeLessThanOrEqual($afterTime + 3600);
    });

    test('handles token data without expiration', function () {
        $storage = new TokenStorage();

        $storage->store(['access_token' => 'permanent-token']);

        expect($storage->isExpired())->toBeFalse();
    });

    test('can get token type', function () {
        $storage = new TokenStorage();

        $storage->store(['token_type' => 'Bearer']);

        $retrieved = $storage->get();

        expect($retrieved['token_type'])->toBe('Bearer');
    });

    test('can get token scopes', function () {
        $storage = new TokenStorage();

        $storage->store(['scope' => 'ai pi name offline_access']);

        $retrieved = $storage->get();

        expect($retrieved['scope'])->toBe('ai pi name offline_access');
    });

    test('handles empty token data gracefully', function () {
        $storage = new TokenStorage();

        $storage->store([]);

        expect($storage->get())->toBe([])
            ->and($storage->has())->toBeTrue();
    });
});
