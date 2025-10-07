<?php

use AlchemicStudio\PontoConnect\Exceptions\ValidationException;
use AlchemicStudio\PontoConnect\Exceptions\PontoException;

describe('ValidationException', function () {
    test('can be instantiated', function () {
        $exception = new ValidationException('Validation failed');

        expect($exception)->toBeInstanceOf(ValidationException::class)
            ->and($exception)->toBeInstanceOf(PontoException::class);
    });

    test('can be thrown and caught', function () {
        expect(fn() => throw new ValidationException('Invalid input'))
            ->toThrow(ValidationException::class, 'Invalid input');
    });

    test('handles missing required field scenario', function () {
        $exception = new ValidationException('Required field "amount" is missing');

        expect($exception->getMessage())->toContain('Required field')
            ->and($exception->getMessage())->toContain('amount');
    });

    test('handles invalid field format scenario', function () {
        $exception = new ValidationException('Invalid IBAN format');

        expect($exception->getMessage())->toContain('Invalid')
            ->and($exception->getMessage())->toContain('format');
    });

    test('handles out of range value scenario', function () {
        $exception = new ValidationException('Amount must be greater than 0');

        expect($exception->getMessage())->toContain('Amount')
            ->and($exception->getMessage())->toContain('greater than');
    });

    test('handles invalid currency code scenario', function () {
        $exception = new ValidationException('Invalid currency code: XYZ');

        expect($exception->getMessage())->toContain('currency code');
    });

    test('handles invalid configuration scenario', function () {
        $exception = new ValidationException('Missing required configuration: client_id');

        expect($exception->getMessage())->toContain('configuration')
            ->and($exception->getMessage())->toContain('client_id');
    });

    test('handles invalid date format scenario', function () {
        $exception = new ValidationException('Invalid date format, expected YYYY-MM-DD');

        expect($exception->getMessage())->toContain('date format')
            ->and($exception->getMessage())->toContain('YYYY-MM-DD');
    });

    test('handles invalid UUID scenario', function () {
        $exception = new ValidationException('Invalid UUID format for account ID');

        expect($exception->getMessage())->toContain('UUID')
            ->and($exception->getMessage())->toContain('account ID');
    });

    test('handles pagination parameter validation', function () {
        $exception = new ValidationException('page[limit] must be between 1 and 100');

        expect($exception->getMessage())->toContain('page[limit]')
            ->and($exception->getMessage())->toContain('between 1 and 100');
    });

    test('can store validation errors array', function () {
        $errors = [
            'amount' => 'Required field',
            'currency' => 'Invalid currency code',
        ];
        $exception = new ValidationException('Validation failed: ' . json_encode($errors));

        expect($exception->getMessage())->toContain('amount')
            ->and($exception->getMessage())->toContain('currency');
    });

    test('can be caught as PontoException', function () {
        expect(fn() => throw new ValidationException('Invalid data'))
            ->toThrow(PontoException::class);
    });
});
