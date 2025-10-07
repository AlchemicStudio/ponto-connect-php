<?php

use AlchemicStudio\PontoConnect\Http\HttpClient;
use AlchemicStudio\PontoConnect\Http\Response;
use AlchemicStudio\PontoConnect\Models\Transaction;
use AlchemicStudio\PontoConnect\Services\TransactionService;
use AlchemicStudio\PontoConnect\Utils\Pagination;

describe('TransactionService', function () {
    test('can be instantiated with HttpClient and account ID', function () {
        $httpClient = Mockery::mock(HttpClient::class);

        $service = new TransactionService($httpClient, 'acc-123');

        expect($service)->toBeInstanceOf(TransactionService::class);
    });

    test('can list transactions for account', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts/acc-123/transactions', [])
            ->andReturn(new Response(200, json_encode([
                'data' => [['id' => 'txn-1', 'attributes' => ['amount' => 100]]],
                'links' => [],
                'meta' => [],
            ])));

        $service = new TransactionService($httpClient, 'acc-123');
        $result = $service->list();

        expect($result)->toBeInstanceOf(Pagination::class);
    });

    test('can list transactions with pagination filters', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts/acc-123/transactions', ['page[limit]' => 20])
            ->andReturn(new Response(200, json_encode(['data' => [], 'links' => [], 'meta' => []])));

        $service = new TransactionService($httpClient, 'acc-123');
        $service->list(['page[limit]' => 20]);

        expect(true)->toBeTrue();
    });

    test('can get single transaction by ID', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts/acc-123/transactions/txn-456', [])
            ->andReturn(new Response(200, json_encode([
                'data' => ['id' => 'txn-456', 'attributes' => ['amount' => 250.50]],
            ])));

        $service = new TransactionService($httpClient, 'acc-123');
        $transaction = $service->get('txn-456');

        expect($transaction)->toBeInstanceOf(Transaction::class)
            ->and($transaction->getId())->toBe('txn-456');
    });

    test('can list updated transactions after synchronization', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/synchronizations/sync-789/updated-transactions', [])
            ->andReturn(new Response(200, json_encode(['data' => [], 'links' => [], 'meta' => []])));

        $service = new TransactionService($httpClient, 'acc-123');
        $result = $service->listUpdated('sync-789');

        expect($result)->toBeInstanceOf(Pagination::class);
    });

    test('validates account ID on instantiation', function () {
        $httpClient = Mockery::mock(HttpClient::class);

        expect(fn () => new TransactionService($httpClient, ''))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('validates transaction ID format', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $service = new TransactionService($httpClient, 'acc-123');

        expect(fn () => $service->get(''))
            ->toThrow(\InvalidArgumentException::class);
    });
})->after(function () {
    Mockery::close();
});
