<?php

use AlchemicStudio\PontoConnect\Models\Account;

describe('Account', function () {
    test('can be instantiated from API response data', function () {
        $data = [
            'id' => '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b',
            'type' => 'account',
            'attributes' => [
                'reference' => 'BE68539007547034',
                'referenceType' => 'IBAN',
                'holderName' => 'John Doe',
                'currency' => 'EUR',
                'availableBalance' => 1000.50,
                'currentBalance' => 1200.75,
                'description' => 'Current Account',
                'subtype' => 'checking',
            ],
        ];

        $account = Account::fromArray($data);

        expect($account)->toBeInstanceOf(Account::class);
    });

    test('can access account id', function () {
        $data = [
            'id' => '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b',
            'attributes' => ['reference' => 'BE68539007547034'],
        ];

        $account = Account::fromArray($data);

        expect($account->getId())->toBe('5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b');
    });

    test('can access account reference (IBAN)', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['reference' => 'BE68539007547034'],
        ];

        $account = Account::fromArray($data);

        expect($account->getReference())->toBe('BE68539007547034');
    });

    test('can access reference type', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['referenceType' => 'IBAN'],
        ];

        $account = Account::fromArray($data);

        expect($account->getReferenceType())->toBe('IBAN');
    });

    test('can access holder name', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['holderName' => 'John Doe'],
        ];

        $account = Account::fromArray($data);

        expect($account->getHolderName())->toBe('John Doe');
    });

    test('can access currency code', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['currency' => 'EUR'],
        ];

        $account = Account::fromArray($data);

        expect($account->getCurrency())->toBe('EUR');
    });

    test('can access available balance', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['availableBalance' => 1000.50],
        ];

        $account = Account::fromArray($data);

        expect($account->getAvailableBalance())->toBe(1000.50);
    });

    test('can access current balance', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['currentBalance' => 1200.75],
        ];

        $account = Account::fromArray($data);

        expect($account->getCurrentBalance())->toBe(1200.75);
    });

    test('can access account description', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['description' => 'Current Account'],
        ];

        $account = Account::fromArray($data);

        expect($account->getDescription())->toBe('Current Account');
    });

    test('can access account subtype', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['subtype' => 'checking'],
        ];

        $account = Account::fromArray($data);

        expect($account->getSubtype())->toBe('checking');
    });

    test('can access account product', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['product' => 'Current account'],
        ];

        $account = Account::fromArray($data);

        expect($account->getProduct())->toBe('Current account');
    });

    test('can check if account is deprecated', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['deprecated' => true],
        ];

        $account = Account::fromArray($data);

        expect($account->isDeprecated())->toBeTrue();
    });

    test('can access authorization expiration date', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['authorizationExpirationExpectedAt' => '2025-07-17T14:14:26.569Z'],
        ];

        $account = Account::fromArray($data);

        expect($account->getAuthorizationExpirationExpectedAt())->toBeInstanceOf(\DateTimeInterface::class);
    });

    test('can access authorized at timestamp', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['authorizedAt' => '2025-04-18T14:14:26.569Z'],
        ];

        $account = Account::fromArray($data);

        expect($account->getAuthorizedAt())->toBeInstanceOf(\DateTimeInterface::class);
    });

    test('can access internal reference', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['internalReference' => '8e49fac4-7eb1-41d2-8c8c-30adc94c56fb'],
        ];

        $account = Account::fromArray($data);

        expect($account->getInternalReference())->toBe('8e49fac4-7eb1-41d2-8c8c-30adc94c56fb');
    });

    test('can convert to array', function () {
        $data = [
            'id' => 'abc123',
            'type' => 'account',
            'attributes' => [
                'reference' => 'BE68539007547034',
                'currency' => 'EUR',
                'holderName' => 'John Doe',
            ],
        ];

        $account = Account::fromArray($data);
        $array = $account->toArray();

        expect($array)->toHaveKey('id')
            ->and($array['id'])->toBe('abc123');
    });

    test('handles negative balance', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['availableBalance' => -465.33],
        ];

        $account = Account::fromArray($data);

        expect($account->getAvailableBalance())->toBe(-465.33);
    });

    test('handles zero balance', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['currentBalance' => 0.0],
        ];

        $account = Account::fromArray($data);

        expect($account->getCurrentBalance())->toBe(0.0);
    });

    test('handles missing optional fields gracefully', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['reference' => 'BE68539007547034'],
        ];

        $account = Account::fromArray($data);

        expect($account->getDescription())->toBeNull();
    });

    test('can access financial institution relationship', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['reference' => 'BE68539007547034'],
            'relationships' => [
                'financialInstitution' => [
                    'data' => [
                        'id' => 'fi123',
                        'type' => 'financialInstitution',
                    ],
                ],
            ],
        ];

        $account = Account::fromArray($data);

        expect($account->getFinancialInstitutionId())->toBe('fi123');
    });

    test('can access latest synchronization metadata', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => ['reference' => 'BE68539007547034'],
            'meta' => [
                'latestSynchronization' => [
                    'id' => 'sync123',
                    'attributes' => ['status' => 'success'],
                ],
                'synchronizedAt' => '2025-04-22T13:32:00.481Z',
            ],
        ];

        $account = Account::fromArray($data);

        expect($account->getSynchronizedAt())->toBeInstanceOf(\DateTimeInterface::class);
    });

    test('can serialize to JSON', function () {
        $data = [
            'id' => 'abc123',
            'attributes' => [
                'reference' => 'BE68539007547034',
                'currency' => 'EUR',
            ],
        ];

        $account = Account::fromArray($data);
        $json = json_encode($account);

        expect($json)->toBeString()
            ->and($json)->toContain('abc123')
            ->and($json)->toContain('BE68539007547034');
    });
});
