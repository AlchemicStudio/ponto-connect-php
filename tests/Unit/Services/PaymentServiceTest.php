<?php

use AlchemicStudio\PontoConnect\Services\PaymentService;
use AlchemicStudio\PontoConnect\Http\HttpClient;
use AlchemicStudio\PontoConnect\Http\Response;
use AlchemicStudio\PontoConnect\Models\Payment;

describe('PaymentService', function () {
    test('can be instantiated with HttpClient and account ID', function () {
        $httpClient = Mockery::mock(HttpClient::class);

        $service = new PaymentService($httpClient, 'acc-123');

        expect($service)->toBeInstanceOf(PaymentService::class);
    });

    test('can create payment', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('post')
            ->with('/accounts/acc-123/payments', Mockery::type('array'), Mockery::type('string'))
            ->andReturn(new Response(201, json_encode([
                'data' => ['id' => 'pay-456', 'attributes' => ['status' => 'pending']],
            ])));

        $service = new PaymentService($httpClient, 'acc-123');
        $payment = $service->create([
            'amount' => 100.00,
            'currency' => 'EUR',
            'creditorName' => 'John Doe',
            'creditorAccountReference' => 'BE68539007547034',
        ]);

        expect($payment)->toBeInstanceOf(Payment::class);
    });

    test('uses idempotency key for payment creation', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('post')
            ->with('/accounts/acc-123/payments', Mockery::type('array'), Mockery::type('string'))
            ->andReturn(new Response(201, json_encode(['data' => ['id' => 'pay-1', 'attributes' => []]])));

        $service = new PaymentService($httpClient, 'acc-123');
        $service->create(['amount' => 100]);

        expect(true)->toBeTrue();
    });

    test('can get payment by ID', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts/acc-123/payments/pay-789', [])
            ->andReturn(new Response(200, json_encode([
                'data' => ['id' => 'pay-789', 'attributes' => ['amount' => 250]],
            ])));

        $service = new PaymentService($httpClient, 'acc-123');
        $payment = $service->get('pay-789');

        expect($payment)->toBeInstanceOf(Payment::class);
    });

    test('can delete payment', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('delete')
            ->with('/accounts/acc-123/payments/pay-789')
            ->andReturn(new Response(204, null));

        $service = new PaymentService($httpClient, 'acc-123');
        $service->delete('pay-789');

        expect(true)->toBeTrue();
    });

    test('validates payment data on creation', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $service = new PaymentService($httpClient, 'acc-123');

        expect(fn() => $service->create([]))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('can create bulk payment', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('post')
            ->with('/accounts/acc-123/bulk-payments', Mockery::type('array'), Mockery::type('string'))
            ->andReturn(new Response(201, json_encode([
                'data' => ['id' => 'bulk-1', 'attributes' => ['status' => 'pending']],
            ])));

        $service = new PaymentService($httpClient, 'acc-123');
        $result = $service->createBulk([
            'reference' => 'Bulk payment',
            'payments' => [
                ['amount' => 100, 'creditorName' => 'Person 1'],
                ['amount' => 200, 'creditorName' => 'Person 2'],
            ],
        ]);

        expect($result)->toBeArray();
    });

    test('can get bulk payment by ID', function () {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('get')
            ->with('/accounts/acc-123/bulk-payments/bulk-456', [])
            ->andReturn(new Response(200, json_encode([
                'data' => ['id' => 'bulk-456', 'attributes' => []],
            ])));

        $service = new PaymentService($httpClient, 'acc-123');
        $result = $service->getBulk('bulk-456');

        expect($result)->toBeArray();
    });

    test('validates account ID on instantiation', function () {
        $httpClient = Mockery::mock(HttpClient::class);

        expect(fn() => new PaymentService($httpClient, ''))
            ->toThrow(\InvalidArgumentException::class);
    });
})->after(function () {
    Mockery::close();
});
