<?php

use AlchemicStudio\PontoConnect\Services\SynchronizationService;
use AlchemicStudio\PontoConnect\Http\HttpClient;
use AlchemicStudio\PontoConnect\Http\Response;

describe('SynchronizationService', function () {
    test('can be instantiated with HttpClient', function () {
        $httpClient = Mockery::mock(HttpClient::class);

        $service = new SynchronizationService($httpClient);

        expect($service)->toBeInstanceOf(SynchronizationService::class);
    });

    test('can create synchronization for account', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('post')
            ->with('/synchronizations', Mockery::type('array'), Mockery::type('string'))
            ->andReturn(new Response(201, json_encode([
                'data' => [
                    'id' => 'sync-123',
                    'attributes' => [
                        'resourceType' => 'account',
                        'resourceId' => 'acc-456',
                        'subtype' => 'accountDetails',
                        'status' => 'pending',
                    ],
                ],
            ])));

        $service = new SynchronizationService($httpClient);
        $result = $service->create('account', 'acc-456', 'accountDetails');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('sync-123');
    });

    test('can create synchronization for transactions', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('post')
            ->andReturn(new Response(201, json_encode([
                'data' => ['id' => 'sync-789', 'attributes' => ['subtype' => 'accountTransactions']],
            ])));

        $service = new SynchronizationService($httpClient);
        $result = $service->create('account', 'acc-123', 'accountTransactions');

        expect($result['attributes']['subtype'])->toBe('accountTransactions');
    });

    test('can get synchronization status by ID', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/synchronizations/sync-456', [])
            ->andReturn(new Response(200, json_encode([
                'data' => [
                    'id' => 'sync-456',
                    'attributes' => ['status' => 'success'],
                ],
            ])));

        $service = new SynchronizationService($httpClient);
        $result = $service->get('sync-456');

        expect($result['attributes']['status'])->toBe('success');
    });

    test('validates resource type on create', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $service = new SynchronizationService($httpClient);

        expect(fn() => $service->create('invalid-type', 'res-123', 'subtype'))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('validates resource ID on create', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $service = new SynchronizationService($httpClient);

        expect(fn() => $service->create('account', '', 'subtype'))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('validates synchronization ID format', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $service = new SynchronizationService($httpClient);

        expect(fn() => $service->get(''))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('uses idempotency key for synchronization creation', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('post')
            ->with('/synchronizations', Mockery::type('array'), Mockery::type('string'))
            ->andReturn(new Response(201, json_encode(['data' => ['id' => 'sync-1', 'attributes' => []]])));

        $service = new SynchronizationService($httpClient);
        $service->create('account', 'acc-123', 'accountDetails');

        expect(true)->toBeTrue();
    });

    test('can handle synchronization with errors', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->andReturn(new Response(200, json_encode([
                'data' => [
                    'id' => 'sync-999',
                    'attributes' => [
                        'status' => 'error',
                        'errors' => [
                            ['code' => 'timeout', 'detail' => 'Connection timeout'],
                        ],
                    ],
                ],
            ])));

        $service = new SynchronizationService($httpClient);
        $result = $service->get('sync-999');

        expect($result['attributes']['status'])->toBe('error')
            ->and($result['attributes']['errors'])->toBeArray();
    });
})->after(function () {
    Mockery::close();
});
