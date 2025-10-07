<?php

use AlchemicStudio\PontoConnect\Auth\AuthProvider;
use AlchemicStudio\PontoConnect\Auth\TokenStorage;
use AlchemicStudio\PontoConnect\Client;
use AlchemicStudio\PontoConnect\Services\AccountService;
use AlchemicStudio\PontoConnect\Services\PaymentService;
use AlchemicStudio\PontoConnect\Services\SynchronizationService;
use AlchemicStudio\PontoConnect\Services\TransactionService;

describe('Ponto Connect API Integration', function () {
    test('can initialize client with configuration', function () {
        $config = [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'base_url' => 'https://api.ibanity.com/ponto-connect',
            'cert_path' => '/path/to/cert.pem',
            'key_path' => '/path/to/key.pem',
        ];

        $client = new Client($config);

        expect($client)->toBeInstanceOf(Client::class);
    });

    test('client provides access to account service', function () {
        $config = [
            'client_id' => 'test-id',
            'client_secret' => 'test-secret',
            'base_url' => 'https://api.test.com',
            'cert_path' => '/cert.pem',
            'key_path' => '/key.pem',
        ];

        $client = new Client($config);
        $accountService = $client->accounts();

        expect($accountService)->toBeInstanceOf(AccountService::class);
    });

    test('client provides access to transaction service for specific account', function () {
        $config = [
            'client_id' => 'test-id',
            'client_secret' => 'test-secret',
            'base_url' => 'https://api.test.com',
            'cert_path' => '/cert.pem',
            'key_path' => '/key.pem',
        ];

        $client = new Client($config);
        $transactionService = $client->transactions('acc-123');

        expect($transactionService)->toBeInstanceOf(TransactionService::class);
    });

    test('client provides access to payment service for specific account', function () {
        $config = [
            'client_id' => 'test-id',
            'client_secret' => 'test-secret',
            'base_url' => 'https://api.test.com',
            'cert_path' => '/cert.pem',
            'key_path' => '/key.pem',
        ];

        $client = new Client($config);
        $paymentService = $client->payments('acc-123');

        expect($paymentService)->toBeInstanceOf(PaymentService::class);
    });

    test('client provides access to synchronization service', function () {
        $config = [
            'client_id' => 'test-id',
            'client_secret' => 'test-secret',
            'base_url' => 'https://api.test.com',
            'cert_path' => '/cert.pem',
            'key_path' => '/key.pem',
        ];

        $client = new Client($config);
        $syncService = $client->synchronizations();

        expect($syncService)->toBeInstanceOf(SynchronizationService::class);
    });

    test('validates required configuration parameters', function () {
        expect(fn () => new Client([]))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('validates client_id is not empty', function () {
        $config = [
            'client_id' => '',
            'client_secret' => 'secret',
            'base_url' => 'https://api.test.com',
            'cert_path' => '/cert.pem',
            'key_path' => '/key.pem',
        ];

        expect(fn () => new Client($config))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('validates base_url format', function () {
        $config = [
            'client_id' => 'id',
            'client_secret' => 'secret',
            'base_url' => 'invalid-url',
            'cert_path' => '/cert.pem',
            'key_path' => '/key.pem',
        ];

        expect(fn () => new Client($config))
            ->toThrow(\InvalidArgumentException::class);
    });
});

describe('OAuth2 Flow Integration', function () {
    test('can generate authorization URL with PKCE', function () {
        $tokenStorage = new TokenStorage();
        $authProvider = new AuthProvider(
            'client-id',
            'client-secret',
            'https://api.ibanity.com',
            $tokenStorage
        );

        $codeVerifier = bin2hex(random_bytes(32));
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $url = $authProvider->getAuthorizationUrl(
            'https://myapp.com/callback',
            $codeChallenge
        );

        expect($url)->toBeString()
            ->and($url)->toContain('code_challenge')
            ->and($url)->toContain('client_id=client-id');
    });

    test('token storage can persist and retrieve tokens', function () {
        $storage = new TokenStorage();

        $tokenData = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires_in' => 3600,
        ];

        $storage->store($tokenData);
        $retrieved = $storage->get();

        expect($retrieved['access_token'])->toBe('test-access-token')
            ->and($retrieved['refresh_token'])->toBe('test-refresh-token');
    });
});

describe('Complete API Workflow', function () {
    test('demonstrates typical API usage flow', function () {
        // This test demonstrates the expected usage pattern
        // In real implementation, HTTP calls would be mocked or use VCR

        $config = [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-secret',
            'base_url' => 'https://api.ibanity.com/ponto-connect',
            'cert_path' => '/path/to/cert.pem',
            'key_path' => '/path/to/key.pem',
        ];

        // Initialize client
        $client = new Client($config);

        // Access accounts would work like:
        // $accounts = $client->accounts()->list();
        // foreach ($accounts as $account) {
        //     $transactions = $client->transactions($account->getId())->list();
        // }

        expect($client)->toBeInstanceOf(Client::class);
    });
});

describe('Error Handling Integration', function () {
    test('client handles missing configuration gracefully', function () {
        expect(fn () => new Client(['client_id' => 'test']))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('services validate account IDs', function () {
        $config = [
            'client_id' => 'id',
            'client_secret' => 'secret',
            'base_url' => 'https://api.test.com',
            'cert_path' => '/cert.pem',
            'key_path' => '/key.pem',
        ];

        $client = new Client($config);

        expect(fn () => $client->transactions(''))
            ->toThrow(\InvalidArgumentException::class);
    });
});
