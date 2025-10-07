<?php

use AlchemicStudio\PontoConnect\Models\Payment;

describe('Payment', function () {
    test('can be instantiated from API response data', function () {
        $data = [
            'id' => 'pay-123',
            'type' => 'payment',
            'attributes' => [
                'amount' => 100.00,
                'currency' => 'EUR',
                'description' => 'Invoice payment',
                'creditorName' => 'ACME Corp',
                'creditorAccountReference' => 'BE68539007547034',
                'creditorAccountReferenceType' => 'IBAN',
                'requestedExecutionDate' => '2025-05-01',
                'status' => 'pending',
            ],
        ];

        $payment = Payment::fromArray($data);

        expect($payment)->toBeInstanceOf(Payment::class);
    });

    test('can access payment id', function () {
        $data = ['id' => 'pay-456', 'attributes' => []];
        $payment = Payment::fromArray($data);

        expect($payment->getId())->toBe('pay-456');
    });

    test('can access amount', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['amount' => 250.50]];
        $payment = Payment::fromArray($data);

        expect($payment->getAmount())->toBe(250.50);
    });

    test('can access currency', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['currency' => 'GBP']];
        $payment = Payment::fromArray($data);

        expect($payment->getCurrency())->toBe('GBP');
    });

    test('can access description', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['description' => 'Supplier payment']];
        $payment = Payment::fromArray($data);

        expect($payment->getDescription())->toBe('Supplier payment');
    });

    test('can access creditor name', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['creditorName' => 'John Doe']];
        $payment = Payment::fromArray($data);

        expect($payment->getCreditorName())->toBe('John Doe');
    });

    test('can access creditor account reference', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['creditorAccountReference' => 'BE68539007547034']];
        $payment = Payment::fromArray($data);

        expect($payment->getCreditorAccountReference())->toBe('BE68539007547034');
    });

    test('can access payment status', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['status' => 'accepted']];
        $payment = Payment::fromArray($data);

        expect($payment->getStatus())->toBe('accepted');
    });

    test('can access requested execution date', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['requestedExecutionDate' => '2025-05-01']];
        $payment = Payment::fromArray($data);

        expect($payment->getRequestedExecutionDate())->toBeInstanceOf(\DateTimeInterface::class);
    });

    test('can access end to end id', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['endToEndId' => 'E2E-123']];
        $payment = Payment::fromArray($data);

        expect($payment->getEndToEndId())->toBe('E2E-123');
    });

    test('can access remittance information', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['remittanceInformation' => 'Invoice 12345']];
        $payment = Payment::fromArray($data);

        expect($payment->getRemittanceInformation())->toBe('Invoice 12345');
    });

    test('can access redirect link', function () {
        $data = [
            'id' => 'pay-123',
            'attributes' => [],
            'links' => ['redirect' => 'https://authorize.myponto.net/payment/123'],
        ];
        $payment = Payment::fromArray($data);

        expect($payment->getRedirectUrl())->toBe('https://authorize.myponto.net/payment/123');
    });

    test('handles different payment statuses', function () {
        $statuses = ['pending', 'accepted', 'rejected', 'executed'];

        foreach ($statuses as $status) {
            $data = ['id' => 'pay-123', 'attributes' => ['status' => $status]];
            $payment = Payment::fromArray($data);

            expect($payment->getStatus())->toBe($status);
        }
    });

    test('can convert to array', function () {
        $data = [
            'id' => 'pay-123',
            'attributes' => ['amount' => 100.00, 'currency' => 'EUR'],
        ];
        $payment = Payment::fromArray($data);

        expect($payment->toArray())->toHaveKey('id');
    });

    test('can serialize to JSON', function () {
        $data = ['id' => 'pay-123', 'attributes' => ['amount' => 100.00]];
        $payment = Payment::fromArray($data);

        expect(json_encode($payment))->toContain('pay-123');
    });
});
