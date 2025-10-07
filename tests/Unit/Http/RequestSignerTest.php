<?php

use AlchemicStudio\PontoConnect\Http\RequestSigner;

describe('RequestSigner', function () {
    test('can be instantiated with certificate details', function () {
        $signer = new RequestSigner(
            'certificate-id-123',
            '/path/to/signature/cert.pem',
            '/path/to/signature/key.pem'
        );

        expect($signer)->toBeInstanceOf(RequestSigner::class);
    });

    test('generates signature headers for request', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('POST', '/accounts/123/payments', '{"amount": 100}');

        expect($headers)->toBeArray()
            ->and($headers)->toHaveKey('Signature');
    });

    test('includes digest header for POST requests', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('POST', '/payments', '{"data": "test"}');

        expect($headers)->toHaveKey('Digest');
    });

    test('includes digest header for PATCH requests', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('PATCH', '/payments/123', '{"status": "updated"}');

        expect($headers)->toHaveKey('Digest');
    });

    test('does not include digest for GET requests', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('GET', '/accounts', null);

        expect($headers)->not->toHaveKey('Digest');
    });

    test('signature includes request-target', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('GET', '/accounts', null);

        expect($headers['Signature'])->toContain('(request-target)');
    });

    test('signature includes host header', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('GET', '/accounts', null);

        expect($headers['Signature'])->toContain('host');
    });

    test('signature includes date header', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('GET', '/accounts', null);

        expect($headers['Signature'])->toContain('date');
    });

    test('signature includes digest for POST', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('POST', '/payments', '{"amount": 100}');

        expect($headers['Signature'])->toContain('digest');
    });

    test('uses SHA-256 for digest', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('POST', '/payments', '{"amount": 100}');

        expect($headers['Digest'])->toContain('SHA-256=');
    });

    test('uses RSA-SHA256 algorithm for signature', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('GET', '/accounts', null);

        expect($headers['Signature'])->toContain('algorithm="rsa-sha256"');
    });

    test('includes certificate ID in signature', function () {
        $signer = new RequestSigner('my-cert-id-789', '/cert.pem', '/key.pem');

        $headers = $signer->sign('GET', '/accounts', null);

        expect($headers['Signature'])->toContain('keyId="my-cert-id-789"');
    });

    test('creates consistent digest for same body', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');
        $body = '{"amount": 100}';

        $headers1 = $signer->sign('POST', '/payments', $body);
        $headers2 = $signer->sign('POST', '/payments', $body);

        expect($headers1['Digest'])->toBe($headers2['Digest']);
    });

    test('handles empty body for POST', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('POST', '/synchronizations', '');

        expect($headers)->toHaveKey('Digest');
    });

    test('handles null body gracefully', function () {
        $signer = new RequestSigner('cert-id', '/cert.pem', '/key.pem');

        $headers = $signer->sign('GET', '/accounts', null);

        expect($headers)->toBeArray();
    });
});
