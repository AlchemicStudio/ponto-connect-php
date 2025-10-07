<?php

use AlchemicStudio\PontoConnect\Utils\Pagination;
use AlchemicStudio\PontoConnect\Http\HttpClient;

describe('Pagination', function () {
    test('can be instantiated with data and metadata', function () {
        $data = [['id' => '1'], ['id' => '2']];
        $meta = ['paging' => ['limit' => 10]];
        $links = ['next' => 'https://api.example.com/next'];

        $pagination = new Pagination($data, $meta, $links);

        expect($pagination)->toBeInstanceOf(Pagination::class);
    });

    test('can retrieve items', function () {
        $data = [['id' => '1'], ['id' => '2'], ['id' => '3']];
        $pagination = new Pagination($data, [], []);

        expect($pagination->items())->toBe($data)
            ->and($pagination->items())->toHaveCount(3);
    });

    test('can retrieve metadata', function () {
        $meta = ['paging' => ['limit' => 10, 'after' => 'cursor123']];
        $pagination = new Pagination([], $meta, []);

        expect($pagination->meta())->toBe($meta);
    });

    test('can retrieve links', function () {
        $links = [
            'first' => 'https://api.example.com/first',
            'next' => 'https://api.example.com/next',
        ];
        $pagination = new Pagination([], [], $links);

        expect($pagination->links())->toBe($links);
    });

    test('can check if has next page', function () {
        $links = ['next' => 'https://api.example.com/next'];
        $pagination = new Pagination([], [], $links);

        expect($pagination->hasNext())->toBeTrue();
    });

    test('can check if has no next page', function () {
        $links = ['first' => 'https://api.example.com/first'];
        $pagination = new Pagination([], [], $links);

        expect($pagination->hasNext())->toBeFalse();
    });

    test('can check if has previous page', function () {
        $links = ['previous' => 'https://api.example.com/previous'];
        $pagination = new Pagination([], [], $links);

        expect($pagination->hasPrevious())->toBeTrue();
    });

    test('can check if has no previous page', function () {
        $links = ['first' => 'https://api.example.com/first'];
        $pagination = new Pagination([], [], $links);

        expect($pagination->hasPrevious())->toBeFalse();
    });

    test('can get next cursor from links', function () {
        $links = ['next' => 'https://api.example.com/accounts?page[after]=cursor123&page[limit]=10'];
        $pagination = new Pagination([], [], $links);

        expect($pagination->getNextCursor())->toBe('cursor123');
    });

    test('can get previous cursor from links', function () {
        $links = ['previous' => 'https://api.example.com/accounts?page[before]=cursor456&page[limit]=10'];
        $pagination = new Pagination([], [], $links);

        expect($pagination->getPreviousCursor())->toBe('cursor456');
    });

    test('returns null when no next cursor available', function () {
        $pagination = new Pagination([], [], []);

        expect($pagination->getNextCursor())->toBeNull();
    });

    test('returns null when no previous cursor available', function () {
        $pagination = new Pagination([], [], []);

        expect($pagination->getPreviousCursor())->toBeNull();
    });

    test('can get current page limit from metadata', function () {
        $meta = ['paging' => ['limit' => 50]];
        $pagination = new Pagination([], $meta, []);

        expect($pagination->getLimit())->toBe(50);
    });

    test('returns default limit when not specified', function () {
        $pagination = new Pagination([], [], []);

        expect($pagination->getLimit())->toBe(10);
    });

    test('can get current cursor from metadata', function () {
        $meta = ['paging' => ['after' => 'cursor789']];
        $pagination = new Pagination([], $meta, []);

        expect($pagination->getCurrentCursor())->toBe('cursor789');
    });

    test('can count total items in current page', function () {
        $data = [['id' => '1'], ['id' => '2'], ['id' => '3']];
        $pagination = new Pagination($data, [], []);

        expect($pagination->count())->toBe(3);
    });

    test('returns zero count for empty page', function () {
        $pagination = new Pagination([], [], []);

        expect($pagination->count())->toBe(0);
    });

    test('can check if page is empty', function () {
        $pagination = new Pagination([], [], []);

        expect($pagination->isEmpty())->toBeTrue();
    });

    test('can check if page is not empty', function () {
        $pagination = new Pagination([['id' => '1']], [], []);

        expect($pagination->isEmpty())->toBeFalse();
    });

    test('can iterate over items', function () {
        $data = [['id' => '1'], ['id' => '2'], ['id' => '3']];
        $pagination = new Pagination($data, [], []);

        $ids = [];
        foreach ($pagination as $item) {
            $ids[] = $item['id'];
        }

        expect($ids)->toBe(['1', '2', '3']);
    });

    test('can be converted to array', function () {
        $data = [['id' => '1'], ['id' => '2']];
        $meta = ['paging' => ['limit' => 10]];
        $links = ['next' => 'https://api.example.com/next'];

        $pagination = new Pagination($data, $meta, $links);
        $array = $pagination->toArray();

        expect($array)->toHaveKeys(['data', 'meta', 'links'])
            ->and($array['data'])->toBe($data)
            ->and($array['meta'])->toBe($meta)
            ->and($array['links'])->toBe($links);
    });

    test('handles cursor-based navigation with after parameter', function () {
        $meta = ['paging' => ['after' => 'abc123', 'limit' => 20]];
        $pagination = new Pagination([], $meta, []);

        expect($pagination->getCurrentCursor())->toBe('abc123')
            ->and($pagination->getLimit())->toBe(20);
    });

    test('handles cursor-based navigation with before parameter', function () {
        $meta = ['paging' => ['before' => 'xyz789', 'limit' => 15]];
        $pagination = new Pagination([], $meta, []);

        expect($pagination->getCurrentCursor())->toBe('xyz789')
            ->and($pagination->getLimit())->toBe(15);
    });
});
