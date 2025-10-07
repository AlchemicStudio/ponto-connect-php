<?php

use AlchemicStudio\PontoConnect\Models\Transaction;

describe('Transaction', function () {
    test('can be instantiated from API response data', function () {
        $data = [
            'id' => 'txn-123',
            'type' => 'transaction',
            'attributes' => [
                'valueDate' => '2025-04-20',
                'executionDate' => '2025-04-20',
                'amount' => 100.50,
                'currency' => 'EUR',
                'description' => 'Payment received',
                'remittanceInformation' => 'Invoice 12345',
                'remittanceInformationType' => 'unstructured',
            ],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction)->toBeInstanceOf(Transaction::class);
    });

    test('can access transaction id', function () {
        $data = [
            'id' => 'txn-456',
            'attributes' => ['amount' => 50.00],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getId())->toBe('txn-456');
    });

    test('can access transaction amount', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['amount' => 250.75],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getAmount())->toBe(250.75);
    });

    test('can access transaction currency', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['currency' => 'USD'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getCurrency())->toBe('USD');
    });

    test('can access description', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['description' => 'Payment received'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getDescription())->toBe('Payment received');
    });

    test('can access remittance information', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['remittanceInformation' => 'Invoice 12345'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getRemittanceInformation())->toBe('Invoice 12345');
    });

    test('can access value date', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['valueDate' => '2025-04-20'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getValueDate())->toBeInstanceOf(\DateTimeInterface::class);
    });

    test('can access execution date', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['executionDate' => '2025-04-20'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getExecutionDate())->toBeInstanceOf(\DateTimeInterface::class);
    });

    test('handles negative amount for debits', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['amount' => -150.00],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getAmount())->toBe(-150.00);
    });

    test('can check if transaction is debit', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['amount' => -100.00],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->isDebit())->toBeTrue();
    });

    test('can check if transaction is credit', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['amount' => 100.00],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->isCredit())->toBeTrue();
    });

    test('can access counterpart name', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['counterpartName' => 'ACME Corp'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getCounterpartName())->toBe('ACME Corp');
    });

    test('can access counterpart reference', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['counterpartReference' => 'BE68539007547034'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getCounterpartReference())->toBe('BE68539007547034');
    });

    test('can access bank transaction code', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['bankTransactionCode' => 'PMNT-RCDT-ESCT'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getBankTransactionCode())->toBe('PMNT-RCDT-ESCT');
    });

    test('can access proprietary bank transaction code', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['proprietaryBankTransactionCode' => 'SEPA_CT'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getProprietaryBankTransactionCode())->toBe('SEPA_CT');
    });

    test('can access end to end id', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['endToEndId' => 'E2E-REF-12345'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getEndToEndId())->toBe('E2E-REF-12345');
    });

    test('can access mandate id', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['mandateId' => 'MANDATE-789'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getMandateId())->toBe('MANDATE-789');
    });

    test('can access creditor id', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['creditorId' => 'CRED-456'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getCreditorId())->toBe('CRED-456');
    });

    test('can access purpose code', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['purposeCode' => 'SALA'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getPurposeCode())->toBe('SALA');
    });

    test('can access internal reference', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['internalReference' => 'INT-REF-999'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getInternalReference())->toBe('INT-REF-999');
    });

    test('can convert to array', function () {
        $data = [
            'id' => 'txn-123',
            'type' => 'transaction',
            'attributes' => [
                'amount' => 100.00,
                'currency' => 'EUR',
                'description' => 'Test transaction',
            ],
        ];

        $transaction = Transaction::fromArray($data);
        $array = $transaction->toArray();

        expect($array)->toHaveKey('id')
            ->and($array['id'])->toBe('txn-123');
    });

    test('handles missing optional fields gracefully', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['amount' => 100.00],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getCounterpartName())->toBeNull()
            ->and($transaction->getMandateId())->toBeNull();
    });

    test('can serialize to JSON', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => [
                'amount' => 100.00,
                'currency' => 'EUR',
            ],
        ];

        $transaction = Transaction::fromArray($data);
        $json = json_encode($transaction);

        expect($json)->toBeString()
            ->and($json)->toContain('txn-123')
            ->and($json)->toContain('100');
    });

    test('handles zero amount', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['amount' => 0.0],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getAmount())->toBe(0.0)
            ->and($transaction->isDebit())->toBeFalse()
            ->and($transaction->isCredit())->toBeFalse();
    });

    test('can access additional information', function () {
        $data = [
            'id' => 'txn-123',
            'attributes' => ['additionalInformation' => 'Extra details here'],
        ];

        $transaction = Transaction::fromArray($data);

        expect($transaction->getAdditionalInformation())->toBe('Extra details here');
    });
});
