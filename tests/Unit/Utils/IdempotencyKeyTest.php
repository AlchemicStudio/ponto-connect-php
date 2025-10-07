<?php

use AlchemicStudio\PontoConnect\Utils\IdempotencyKey;

describe('IdempotencyKey', function () {
    test('can generate a UUID v4 key', function () {
        $key = IdempotencyKey::generate();

        expect($key)->toBeString()
            ->and(strlen($key))->toBe(36);
    });

    test('generates valid UUID v4 format', function () {
        $key = IdempotencyKey::generate();
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        expect($key)->toMatch($pattern);
    });

    test('generates unique keys on each call', function () {
        $key1 = IdempotencyKey::generate();
        $key2 = IdempotencyKey::generate();

        expect($key1)->not->toBe($key2);
    });

    test('generates multiple unique keys', function () {
        $keys = [];
        for ($i = 0; $i < 100; $i++) {
            $keys[] = IdempotencyKey::generate();
        }

        $uniqueKeys = array_unique($keys);

        expect($uniqueKeys)->toHaveCount(100);
    });

    test('validates correct UUID v4 format', function () {
        $validKey = '550e8400-e29b-41d4-a716-446655440000';

        expect(IdempotencyKey::isValid($validKey))->toBeTrue();
    });

    test('validates generated key is valid', function () {
        $key = IdempotencyKey::generate();

        expect(IdempotencyKey::isValid($key))->toBeTrue();
    });

    test('rejects invalid UUID format', function () {
        expect(IdempotencyKey::isValid('not-a-uuid'))->toBeFalse();
    });

    test('rejects empty string', function () {
        expect(IdempotencyKey::isValid(''))->toBeFalse();
    });

    test('rejects null value', function () {
        expect(IdempotencyKey::isValid(null))->toBeFalse();
    });

    test('rejects UUID v1 format', function () {
        $uuidV1 = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

        expect(IdempotencyKey::isValid($uuidV1))->toBeFalse();
    });

    test('rejects UUID v3 format', function () {
        $uuidV3 = '6fa459ea-ee8a-3ca4-894e-db77e160355e';

        expect(IdempotencyKey::isValid($uuidV3))->toBeFalse();
    });

    test('rejects UUID v5 format', function () {
        $uuidV5 = '886313e1-3b8a-5372-9b90-0c9aee199e5d';

        expect(IdempotencyKey::isValid($uuidV5))->toBeFalse();
    });

    test('rejects UUID with wrong length', function () {
        expect(IdempotencyKey::isValid('550e8400-e29b-41d4-a716-44665544000'))->toBeFalse();
    });

    test('rejects UUID with invalid characters', function () {
        expect(IdempotencyKey::isValid('550e8400-e29b-41d4-a716-44665544000g'))->toBeFalse();
    });

    test('rejects UUID without hyphens', function () {
        expect(IdempotencyKey::isValid('550e8400e29b41d4a716446655440000'))->toBeFalse();
    });

    test('handles case insensitivity in validation', function () {
        $upperKey = '550E8400-E29B-41D4-A716-446655440000';
        $lowerKey = '550e8400-e29b-41d4-a716-446655440000';

        expect(IdempotencyKey::isValid($upperKey))->toBeTrue()
            ->and(IdempotencyKey::isValid($lowerKey))->toBeTrue();
    });

    test('can normalize UUID to lowercase', function () {
        $key = IdempotencyKey::generate();
        $normalized = IdempotencyKey::normalize($key);

        expect($normalized)->toBe(strtolower($key));
    });

    test('normalizes uppercase UUID', function () {
        $upperKey = '550E8400-E29B-41D4-A716-446655440000';
        $normalized = IdempotencyKey::normalize($upperKey);

        expect($normalized)->toBe('550e8400-e29b-41d4-a716-446655440000');
    });

    test('generated key is already lowercase', function () {
        $key = IdempotencyKey::generate();

        expect($key)->toBe(strtolower($key));
    });

    test('can use as array key', function () {
        $key = IdempotencyKey::generate();
        $data = [$key => 'value'];

        expect($data[$key])->toBe('value');
    });

    test('maintains uniqueness across rapid generation', function () {
        $keys = [];
        for ($i = 0; $i < 1000; $i++) {
            $keys[] = IdempotencyKey::generate();
        }

        expect(count(array_unique($keys)))->toBe(1000);
    });
});
