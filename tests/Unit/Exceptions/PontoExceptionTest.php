<?php

use AlchemicStudio\PontoConnect\Exceptions\PontoException;

describe('PontoException', function () {
    test('can be instantiated', function () {
        $exception = new PontoException('Test error');

        expect($exception)->toBeInstanceOf(PontoException::class)
            ->and($exception)->toBeInstanceOf(\Exception::class);
    });

    test('can be thrown and caught', function () {
        expect(fn () => throw new PontoException('Test error'))
            ->toThrow(PontoException::class, 'Test error');
    });

    test('stores error message', function () {
        $message = 'Something went wrong';
        $exception = new PontoException($message);

        expect($exception->getMessage())->toBe($message);
    });

    test('stores error code', function () {
        $exception = new PontoException('Error', 500);

        expect($exception->getCode())->toBe(500);
    });

    test('stores previous exception', function () {
        $previous = new \Exception('Previous error');
        $exception = new PontoException('Current error', 0, $previous);

        expect($exception->getPrevious())->toBe($previous);
    });

    test('has default code of zero', function () {
        $exception = new PontoException('Error');

        expect($exception->getCode())->toBe(0);
    });

    test('can be converted to string', function () {
        $exception = new PontoException('Test error');
        $string = (string) $exception;

        expect($string)->toContain('Test error')
            ->and($string)->toContain('PontoException');
    });
});
