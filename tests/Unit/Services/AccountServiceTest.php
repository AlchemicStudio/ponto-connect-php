<?php

use AlchemicStudio\PontoConnect\Http\HttpClient;
use AlchemicStudio\PontoConnect\Http\Response;
use AlchemicStudio\PontoConnect\Models\Account;
use AlchemicStudio\PontoConnect\Services\AccountService;
use AlchemicStudio\PontoConnect\Utils\Pagination;

describe('AccountService', function () {
    test('can be instantiated with HttpClient', function () {
        $httpClient = Mockery::mock(HttpClient::class);

        $service = new AccountService($httpClient);

        expect($service)->toBeInstanceOf(AccountService::class);
    });

    test('can list accounts with default pagination', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts', [])
            ->andReturn(new Response(200, json_encode([
                'data' => [['id' => 'acc-1', 'attributes' => []]],
                'links' => [],
                'meta' => ['paging' => ['limit' => 10]],
            ])));

        $service = new AccountService($httpClient);
        $result = $service->list();

        expect($result)->toBeInstanceOf(Pagination::class);
    });

    test('can list accounts with custom pagination filters', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts', ['page[limit]' => 50, 'page[after]' => 'cursor-123'])
            ->andReturn(new Response(200, json_encode(['data' => [], 'links' => [], 'meta' => []])));

        $service = new AccountService($httpClient);
        $result = $service->list(['page[limit]' => 50, 'page[after]' => 'cursor-123']);

        expect($result)->toBeInstanceOf(Pagination::class);
    });

    test('can get single account by ID', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts/acc-123', [])
            ->andReturn(new Response(200, json_encode([
                'data' => ['id' => 'acc-123', 'attributes' => ['reference' => 'BE123']],
            ])));

        $service = new AccountService($httpClient);
        $account = $service->get('acc-123');

        expect($account)->toBeInstanceOf(Account::class)
            ->and($account->getId())->toBe('acc-123');
    });

    test('can delete account', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('delete')
            ->with('/accounts/acc-123')
            ->andReturn(new Response(204, null));

        $service = new AccountService($httpClient);
        $service->delete('acc-123');

        expect(true)->toBeTrue();
    });

    test('throws exception for invalid account ID format', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $service = new AccountService($httpClient);

        expect(fn () => $service->get(''))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('returns pagination with account models', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->andReturn(new Response(200, json_encode([
                'data' => [
                    ['id' => 'acc-1', 'attributes' => ['reference' => 'BE1']],
                    ['id' => 'acc-2', 'attributes' => ['reference' => 'BE2']],
                ],
                'links' => [],
                'meta' => [],
            ])));

        $service = new AccountService($httpClient);
        $pagination = $service->list();

        expect($pagination->count())->toBe(2);
    });

    test('validates pagination limit range', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $service = new AccountService($httpClient);

        expect(fn () => $service->list(['page[limit]' => 150]))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('can create reauthorization request', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('post')
            ->with('/accounts/acc-123/reauthorization-requests', [], Mockery::type('string'))
            ->andReturn(new Response(201, json_encode([
                'data' => ['id' => 'reauth-123', 'attributes' => []],
            ])));

        $service = new AccountService($httpClient);
        $result = $service->createReauthorizationRequest('acc-123');

        expect($result)->toBeArray();
    });
})->after(function () {
    Mockery::close();
});
