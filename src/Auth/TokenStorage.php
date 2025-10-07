<?php

declare(strict_types=1);

namespace AlchemicStudio\PontoConnect\Auth;

class TokenStorage
{
    private ?array $tokenData = null;

    public function store(array $tokenData): void
    {
        $this->tokenData = $tokenData;
    }

    public function get(): ?array
    {
        return $this->tokenData;
    }

    public function clear(): void
    {
        $this->tokenData = null;
    }

    public function has(): bool
    {
        return $this->tokenData !== null;
    }

    public function isExpired(): bool
    {
        if (! $this->has()) {
            return true;
        }

        if (! isset($this->tokenData['expires_at'])) {
            return false;
        }

        return $this->tokenData['expires_at'] < time();
    }
}
