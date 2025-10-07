<?php

declare(strict_types=1);

namespace AlchemicStudio\PontoConnect\Utils;

class Pagination implements \Iterator, \Countable
{
    private int $position = 0;

    public function __construct(
        private array $data,
        private array $meta,
        private array $links
    ) {
    }

    public function items(): array
    {
        return $this->data;
    }

    public function meta(): array
    {
        return $this->meta;
    }

    public function links(): array
    {
        return $this->links;
    }

    public function hasNext(): bool
    {
        return isset($this->links['next']);
    }

    public function hasPrevious(): bool
    {
        return isset($this->links['previous']);
    }

    public function getNextCursor(): ?string
    {
        if (! $this->hasNext()) {
            return null;
        }

        return $this->extractCursorFromUrl($this->links['next'], 'page[after]');
    }

    public function getPreviousCursor(): ?string
    {
        if (! $this->hasPrevious()) {
            return null;
        }

        return $this->extractCursorFromUrl($this->links['previous'], 'page[before]');
    }

    public function getLimit(): int
    {
        return $this->meta['paging']['limit'] ?? 10;
    }

    public function getCurrentCursor(): ?string
    {
        return $this->meta['paging']['after'] ?? $this->meta['paging']['before'] ?? null;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'meta' => $this->meta,
            'links' => $this->links,
        ];
    }

    // Iterator interface methods
    public function current(): mixed
    {
        return $this->data[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    private function extractCursorFromUrl(string $url, string $paramName): ?string
    {
        $parsed = parse_url($url);
        if (! isset($parsed['query'])) {
            return null;
        }

        parse_str($parsed['query'], $queryParams);

        // Handle bracketed parameter names like 'page[after]'
        if (preg_match('/^(\w+)\[(\w+)\]$/', $paramName, $matches)) {
            $key = $matches[1];
            $subKey = $matches[2];

            return $queryParams[$key][$subKey] ?? null;
        }

        return $queryParams[$paramName] ?? null;
    }
}
