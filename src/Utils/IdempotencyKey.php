<?php

declare(strict_types=1);

namespace AlchemicStudio\PontoConnect\Utils;

class IdempotencyKey
{
    public static function generate(): string
    {
        $data = random_bytes(16);

        // Set version (4) and variant bits
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return strtolower(sprintf(
            '%08s-%04s-%04s-%04s-%012s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        ));
    }

    public static function isValid(?string $key): bool
    {
        if ($key === null || $key === '') {
            return false;
        }

        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        return preg_match($pattern, $key) === 1;
    }

    public static function normalize(string $key): string
    {
        return strtolower($key);
    }
}
