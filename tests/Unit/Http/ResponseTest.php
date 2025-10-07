<?php

use AlchemicStudio\PontoConnect\Http\Response;

describe('Response', function () {
    test('can be instantiated with status code and body', function () {
        $response = new Response(200, '{"data": "test"}', ['Content-Type' => 'application/json']);

        expect($response)->toBeInstanceOf(Response::class);
    });

    test('can access status code', function () {
        $response = new Response(201, '{}');

        expect($response->getStatusCode())->toBe(201);
    });

    test('can access raw body', function () {
        $body = '{"id": "123", "name": "test"}';
        $response = new Response(200, $body);

        expect($response->getBody())->toBe($body);
    });

    test('can parse JSON body', function () {
        $response = new Response(200, '{"id": "123", "name": "test"}');

        $data = $response->json();

        expect($data)->toBeArray()
            ->and($data['id'])->toBe('123')
            ->and($data['name'])->toBe('test');
    });

    test('can access headers', function () {
        $headers = ['Content-Type' => 'application/json', 'X-Request-Id' => '456'];
        $response = new Response(200, '{}', $headers);

        expect($response->getHeaders())->toBe($headers);
    });

    test('can check if status is successful', function () {
        $response = new Response(200, '{}');

        expect($response->isSuccessful())->toBeTrue();
    });

    test('recognizes 2xx as successful', function () {
        foreach ([200, 201, 204] as $code) {
            $response = new Response($code, '{}');
            expect($response->isSuccessful())->toBeTrue();
        }
    });

    test('recognizes 4xx as not successful', function () {
        $response = new Response(404, '{}');

        expect($response->isSuccessful())->toBeFalse();
    });

    test('recognizes 5xx as not successful', function () {
        $response = new Response(500, '{}');

        expect($response->isSuccessful())->toBeFalse();
    });

    test('can get specific header', function () {
        $headers = ['X-Request-Id' => 'req-123'];
        $response = new Response(200, '{}', $headers);

        expect($response->getHeader('X-Request-Id'))->toBe('req-123');
    });

    test('returns null for missing header', function () {
        $response = new Response(200, '{}');

        expect($response->getHeader('Missing-Header'))->toBeNull();
    });

    test('can check if header exists', function () {
        $headers = ['Content-Type' => 'application/json'];
        $response = new Response(200, '{}', $headers);

        expect($response->hasHeader('Content-Type'))->toBeTrue()
            ->and($response->hasHeader('Missing'))->toBeFalse();
    });

    test('handles empty body', function () {
        $response = new Response(204, '');

        expect($response->getBody())->toBe('');
    });

    test('handles null body', function () {
        $response = new Response(204, null);

        expect($response->getBody())->toBeNull();
    });

    test('can convert to array', function () {
        $response = new Response(200, '{"key": "value"}', ['Header' => 'value']);

        $array = $response->toArray();

        expect($array)->toHaveKeys(['statusCode', 'body', 'headers']);
    });

    test('handles malformed JSON gracefully', function () {
        $response = new Response(200, 'not-json');

        expect(fn () => $response->json())->toThrow(\JsonException::class);
    });

    test('can access data from JSON:API response', function () {
        $body = json_encode([
            'data' => ['id' => '123', 'type' => 'account'],
            'meta' => ['total' => 1],
        ]);
        $response = new Response(200, $body);

        $json = $response->json();

        expect($json['data']['id'])->toBe('123')
            ->and($json['meta']['total'])->toBe(1);
    });

    test('can access pagination links', function () {
        $body = json_encode([
            'data' => [],
            'links' => [
                'next' => 'https://api.test.com/next',
                'prev' => 'https://api.test.com/prev',
            ],
        ]);
        $response = new Response(200, $body);

        $json = $response->json();

        expect($json['links'])->toHaveKey('next')
            ->and($json['links'])->toHaveKey('prev');
    });
});
