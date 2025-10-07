# Ponto Connect PHP Library - Development Documentation

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Installation](#installation)
4. [Project Structure](#project-structure)
5. [Architecture & Modules](#architecture--modules)
6. [Core Classes](#core-classes)
7. [Usage Examples](#usage-examples)
8. [Configuration](#configuration)
9. [Authentication Strategy](#authentication-strategy)
10. [Security Best Practices](#security-best-practices)
11. [Testing](#testing)
12. [Framework Integration](#framework-integration)
13. [API Compliance](#api-compliance)
14. [Versioning & CI](#versioning--ci)

---

## Overview

This library provides a comprehensive PHP implementation of the **Ponto Connect API v2** by Ibanity. It enables developers to:

- **Authenticate** users via OAuth2 with PKCE
- **Retrieve account information** (accounts, transactions, pending transactions)
- **Initiate payments** (single payments, bulk payments, payment requests)
- **Synchronize data** asynchronously from financial institutions
- **Handle pagination** using cursor-based navigation
- **Implement idempotency** for safe request retries
- **Sign payment requests** with HTTP signatures (required for production)

### Goals

- Full compliance with Ponto Connect API v2
- Clean, typed PHP 8.4+ implementation following PSR-4 and PSR-12
- Comprehensive error handling and retry strategies
- Production-ready with TLS mutual authentication
- Easy integration with popular frameworks (Laravel, Symfony)

---

## Prerequisites

### PHP Version
- **PHP 8.4+** required

### Required PHP Extensions
- `ext-curl` - HTTP client communication
- `ext-json` - JSON encoding/decoding
- `ext-openssl` - TLS/SSL and cryptographic operations
- `ext-mbstring` - String handling

### Development Tools
- **Composer** 2.x for dependency management
- **Git** for version control

### API Access
- Ponto Connect account via [Ibanity Developer Portal](https://developer.ibanity.com/)
- Client credentials (client_id, client_secret)
- TLS client certificate and private key
- Signature certificate (for production payment signing)

---

## Installation

### Via Composer

```bash
composer require alchemicstudio/ponto-connect-php
```

### Manual Installation

```json
{
  "require": {
    "alchemicstudio/ponto-connect-php": "^1.0",
    "guzzlehttp/guzzle": "^7.5",
    "php": "^8.4"
  },
  "autoload": {
    "psr-4": {
      "AlchemicStudio\\Ponto\\": "src/"
    }
  }
}
```

Then run:
```bash
composer install
```

---

## Project Structure

Recommended project layout:

```
ponto-connect-php/
├── src/
│   ├── Client.php                    # Main entry point
│   ├── Auth/
│   │   ├── AuthProvider.php          # OAuth2 token management
│   │   └── TokenStorage.php          # Token persistence
│   ├── Http/
│   │   ├── HttpClient.php            # HTTP communication layer
│   │   ├── RequestSigner.php         # HTTP signature generation
│   │   └── Response.php              # API response wrapper
│   ├── Services/
│   │   ├── AccountService.php        # Account operations
│   │   ├── TransactionService.php    # Transaction retrieval
│   │   ├── PaymentService.php        # Payment initiation
│   │   └── SynchronizationService.php # Data sync operations
│   ├── Models/
│   │   ├── Account.php               # Account model
│   │   ├── Transaction.php           # Transaction model
│   │   ├── Payment.php               # Payment model
│   │   ├── BulkPayment.php           # Bulk payment model
│   │   └── Synchronization.php       # Synchronization model
│   ├── Exceptions/
│   │   ├── PontoException.php        # Base exception
│   │   ├── AuthenticationException.php
│   │   ├── ValidationException.php
│   │   ├── ApiException.php
│   │   └── NetworkException.php
│   └── Utils/
│       ├── Pagination.php            # Cursor-based pagination helper
│       └── IdempotencyKey.php        # UUID V4 generator
├── tests/
│   ├── Unit/
│   └── Integration/
├── examples/
│   ├── authenticate.php
│   ├── list-transactions.php
│   └── create-payment.php
└── composer.json
```

---

## Architecture & Modules

### Module Breakdown

#### 1. **Authentication Module** (`Auth/`)
- OAuth2 authorization code flow with PKCE
- Automatic token refresh using refresh_token
- Secure token storage and retrieval
- TLS mutual authentication with client certificates

#### 2. **HTTP Client Module** (`Http/`)
- Guzzle-based HTTP client with retry logic
- Request/response handling
- HTTP signature generation for payments
- Certificate-based authentication
- Idempotency key injection

#### 3. **Services Module** (`Services/`)
- High-level API for each resource type
- Business logic encapsulation
- Pagination handling
- Error transformation

#### 4. **Models Module** (`Models/`)
- Strongly-typed data objects
- JSON serialization/deserialization
- Validation logic

#### 5. **Exceptions Module** (`Exceptions/`)
- Hierarchical exception structure
- Context-aware error messages
- HTTP status code mapping

#### 6. **Utils Module** (`Utils/`)
- Pagination helpers
- Idempotency key generation
- Common utilities

---

## Core Classes

### 1. Client (`AlchemicStudio\Ponto\Client`)

Main entry point for the library.

**Namespace:** `AlchemicStudio\Ponto`

**Properties:**
- `private HttpClient $httpClient`
- `private AuthProvider $authProvider`
- `private array $config`

**Constructor:**
```php
public function __construct(array $config)
```

**Parameters:**
- `$config` - Configuration array with keys: `client_id`, `client_secret`, `base_url`, `cert_path`, `key_path`, `signature_cert_id`, `signature_key_path`

**Public Methods:**

```php
public function accounts(): AccountService
```
Returns account service instance.

```php
public function transactions(string $accountId): TransactionService
```
Returns transaction service for specific account.

```php
public function payments(string $accountId): PaymentService
```
Returns payment service for specific account.

```php
public function synchronizations(): SynchronizationService
```
Returns synchronization service instance.

**Throws:**
- `ValidationException` - Invalid configuration

---

### 2. AuthProvider (`AlchemicStudio\Ponto\Auth\AuthProvider`)

Manages OAuth2 authentication flow.

**Namespace:** `AlchemicStudio\Ponto\Auth`

**Properties:**
- `private string $clientId`
- `private string $clientSecret`
- `private string $baseUrl`
- `private TokenStorage $tokenStorage`
- `private ?string $accessToken = null`
- `private ?string $refreshToken = null`
- `private ?int $expiresAt = null`

**Public Methods:**

```php
public function __construct(
    string $clientId,
    string $clientSecret,
    string $baseUrl,
    TokenStorage $tokenStorage
)
```

```php
public function getAuthorizationUrl(
    string $redirectUri,
    string $codeChallenge,
    array $scopes = ['ai', 'pi', 'name', 'offline_access']
): string
```
Generates authorization URL for user consent.
- **Returns:** Authorization URL
- **Throws:** `ValidationException`

```php
public function exchangeAuthorizationCode(
    string $code,
    string $codeVerifier,
    string $redirectUri
): array
```
Exchanges authorization code for tokens.
- **Returns:** Array with `access_token`, `refresh_token`, `expires_in`, `scope`
- **Throws:** `AuthenticationException`, `ApiException`

```php
public function getAccessToken(): string
```
Returns valid access token, refreshing if needed.
- **Returns:** Access token string
- **Throws:** `AuthenticationException`

```php
public function refreshAccessToken(): array
```
Refreshes access token using refresh token.
- **Returns:** New token data
- **Throws:** `AuthenticationException`

```php
public function revokeRefreshToken(string $refreshToken): void
```
Revokes a refresh token.
- **Throws:** `ApiException`

---

### 3. HttpClient (`AlchemicStudio\Ponto\Http\HttpClient`)

Handles HTTP communication with Ponto API.

**Namespace:** `AlchemicStudio\Ponto\Http`

**Properties:**
- `private \GuzzleHttp\Client $client`
- `private AuthProvider $authProvider`
- `private ?RequestSigner $signer`
- `private int $maxRetries = 3`

**Public Methods:**

```php
public function __construct(
    string $baseUrl,
    string $certPath,
    string $keyPath,
    AuthProvider $authProvider,
    ?RequestSigner $signer = null
)
```

```php
public function get(string $uri, array $query = []): Response
```
Performs GET request.
- **Throws:** `ApiException`, `NetworkException`

```php
public function post(string $uri, array $data = [], ?string $idempotencyKey = null): Response
```
Performs POST request with optional idempotency.
- **Throws:** `ApiException`, `NetworkException`

```php
public function delete(string $uri): Response
```
Performs DELETE request.
- **Throws:** `ApiException`, `NetworkException`

```php
public function patch(string $uri, array $data = [], ?string $idempotencyKey = null): Response
```
Performs PATCH request with optional idempotency.
- **Throws:** `ApiException`, `NetworkException`

---

### 4. TransactionService (`AlchemicStudio\Ponto\Services\TransactionService`)

Manages transaction operations.

**Namespace:** `AlchemicStudio\Ponto\Services`

**Properties:**
- `private HttpClient $httpClient`
- `private string $accountId`

**Public Methods:**

```php
public function __construct(HttpClient $httpClient, string $accountId)
```

```php
public function list(array $filters = []): Pagination
```
Lists transactions with filters.
- **Parameters:**
  - `$filters['page[limit]']` (int, 1-100): Results per page
  - `$filters['page[before]']` (string): Cursor for previous page
  - `$filters['page[after]']` (string): Cursor for next page
- **Returns:** Pagination object with Transaction models
- **Throws:** `ApiException`

```php
public function get(string $transactionId): Transaction
```
Retrieves single transaction.
- **Returns:** Transaction model
- **Throws:** `ApiException`

```php
public function listUpdated(string $synchronizationId, array $filters = []): Pagination
```
Lists transactions updated during synchronization.
- **Returns:** Pagination object
- **Throws:** `ApiException`

---

### 5. PaymentService (`AlchemicStudio\Ponto\Services\PaymentService`)

Manages payment initiation.

**Namespace:** `AlchemicStudio\Ponto\Services`

**Properties:**
- `private HttpClient $httpClient`
- `private string $accountId`

**Public Methods:**

```php
public function __construct(HttpClient $httpClient, string $accountId)
```

```php
public function create(array $paymentData, ?string $idempotencyKey = null): Payment
```
Creates a payment.
- **Parameters:**
  - `$paymentData`: Required: `currency`, `amount`, `creditorName`, `creditorAccountReference`, `creditorAccountReferenceType`
  - Optional: `remittanceInformation`, `remittanceInformationType`, `creditorAgent`, `creditorAgentType`, `endToEndId`, `requestedExecutionDate`, `redirectUri`
- **Returns:** Payment model with redirect URL
- **Throws:** `ValidationException`, `ApiException`

```php
public function get(string $paymentId): Payment
```
Retrieves payment details.
- **Returns:** Payment model
- **Throws:** `ApiException`

```php
public function delete(string $paymentId): void
```
Cancels a payment.
- **Throws:** `ApiException`

```php
public function createBulk(array $bulkPaymentData, ?string $idempotencyKey = null): BulkPayment
```
Creates bulk payment.
- **Parameters:**
  - `$bulkPaymentData`: Required: `reference`, `payments` array
  - Optional: `redirectUri`, `requestedExecutionDate`, `batchBookingPreferred`
- **Returns:** BulkPayment model
- **Throws:** `ValidationException`, `ApiException`

---

### 6. Models

#### Account (`AlchemicStudio\Ponto\Models\Account`)

**Properties:**
```php
public readonly string $id;
public readonly string $reference;
public readonly string $referenceType;
public readonly string $currency;
public readonly string $holderName;
public readonly float $currentBalance;
public readonly float $availableBalance;
public readonly string $product;
public readonly string $subtype;
public readonly \DateTimeImmutable $authorizedAt;
public readonly ?string $description;
public readonly bool $deprecated;
```

**Methods:**
```php
public static function fromArray(array $data): self
public function toArray(): array
```

---

#### Transaction (`AlchemicStudio\Ponto\Models\Transaction`)

**Properties:**
```php
public readonly string $id;
public readonly float $amount;
public readonly string $currency;
public readonly string $description;
public readonly \DateTimeImmutable $executionDate;
public readonly \DateTimeImmutable $valueDate;
public readonly ?string $remittanceInformation;
public readonly ?string $remittanceInformationType;
public readonly ?string $counterpartName;
public readonly ?string $counterpartReference;
public readonly ?string $endToEndId;
public readonly string $digest;
public readonly \DateTimeImmutable $createdAt;
public readonly \DateTimeImmutable $updatedAt;
```

**Methods:**
```php
public static function fromArray(array $data): self
public function toArray(): array
```

---

#### Payment (`AlchemicStudio\Ponto\Models\Payment`)

**Properties:**
```php
public readonly string $id;
public readonly string $status;
public readonly float $amount;
public readonly string $currency;
public readonly string $creditorName;
public readonly string $creditorAccountReference;
public readonly string $creditorAccountReferenceType;
public readonly ?string $creditorAgent;
public readonly ?string $creditorAgentType;
public readonly ?string $remittanceInformation;
public readonly ?string $remittanceInformationType;
public readonly ?string $endToEndId;
public readonly ?\DateTimeImmutable $requestedExecutionDate;
public readonly ?string $redirectUrl;
```

**Methods:**
```php
public static function fromArray(array $data): self
public function toArray(): array
public function isPending(): bool
public function isCompleted(): bool
```

---

### 7. Exceptions

#### PontoException (Base)
```php
namespace AlchemicStudio\Ponto\Exceptions;

class PontoException extends \Exception
{
    protected array $context = [];
    
    public function getContext(): array
    public function setContext(array $context): void
}
```

#### AuthenticationException
```php
class AuthenticationException extends PontoException {}
```

#### ValidationException
```php
class ValidationException extends PontoException {}
```

#### ApiException
```php
class ApiException extends PontoException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?array $errors = null,
        public readonly ?string $requestId = null
    ) {}
}
```

#### NetworkException
```php
class NetworkException extends PontoException {}
```

---

## Usage Examples

### 1. Basic Setup and Authentication

```php
<?php

require_once 'vendor/autoload.php';

use AlchemicStudio\Ponto\Client;
use AlchemicStudio\Ponto\Auth\AuthProvider;
use AlchemicStudio\Ponto\Exceptions\AuthenticationException;

// Configuration
$config = [
    'client_id' => getenv('PONTO_CLIENT_ID'),
    'client_secret' => getenv('PONTO_CLIENT_SECRET'),
    'base_url' => getenv('PONTO_BASE_URL') ?: 'https://api.ibanity.com/ponto-connect',
    'cert_path' => getenv('PONTO_CERT_PATH'),
    'key_path' => getenv('PONTO_KEY_PATH'),
    'signature_cert_id' => getenv('PONTO_SIGNATURE_CERT_ID'),
    'signature_key_path' => getenv('PONTO_SIGNATURE_KEY_PATH'),
];

try {
    // Initialize client
    $ponto = new Client($config);
    
    // Generate PKCE parameters
    $codeVerifier = bin2hex(random_bytes(32));
    $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    
    // Get authorization URL
    $authUrl = $ponto->getAuthProvider()->getAuthorizationUrl(
        redirectUri: 'https://your-app.com/callback',
        codeChallenge: $codeChallenge,
        scopes: ['ai', 'pi', 'name', 'offline_access']
    );
    
    echo "Visit this URL to authorize: {$authUrl}\n";
    
    // After user authorizes and you receive the code in callback:
    // $code = $_GET['code'];
    
    // Exchange authorization code for tokens
    $tokens = $ponto->getAuthProvider()->exchangeAuthorizationCode(
        code: $code,
        codeVerifier: $codeVerifier,
        redirectUri: 'https://your-app.com/callback'
    );
    
    echo "Access token obtained: {$tokens['access_token']}\n";
    echo "Expires in: {$tokens['expires_in']} seconds\n";
    
} catch (AuthenticationException $e) {
    echo "Authentication failed: {$e->getMessage()}\n";
    exit(1);
}
```

---

### 2. List Accounts

```php
<?php

use AlchemicStudio\Ponto\Client;
use AlchemicStudio\Ponto\Exceptions\ApiException;

$ponto = new Client($config);

try {
    $accountService = $ponto->accounts();
    $accounts = $accountService->list(['page[limit]' => 10]);
    
    foreach ($accounts->items() as $account) {
        echo "Account ID: {$account->id}\n";
        echo "Reference: {$account->reference}\n";
        echo "Holder: {$account->holderName}\n";
        echo "Balance: {$account->availableBalance} {$account->currency}\n";
        echo "---\n";
    }
    
} catch (ApiException $e) {
    echo "API Error: {$e->getMessage()}\n";
    echo "Status Code: {$e->statusCode}\n";
    echo "Request ID: {$e->requestId}\n";
}
```

---

### 3. Fetch Transactions with Pagination and Filters

```php
<?php

use AlchemicStudio\Ponto\Client;
use AlchemicStudio\Ponto\Models\Transaction;

$ponto = new Client($config);
$accountId = '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b';

try {
    $transactionService = $ponto->transactions($accountId);
    
    // Initial request with limit
    $pagination = $transactionService->list([
        'page[limit]' => 20,
    ]);
    
    $allTransactions = [];
    
    do {
        foreach ($pagination->items() as $transaction) {
            echo "Transaction: {$transaction->id}\n";
            echo "Amount: {$transaction->amount} {$transaction->currency}\n";
            echo "Description: {$transaction->description}\n";
            echo "Date: {$transaction->executionDate->format('Y-m-d')}\n";
            echo "Counterpart: {$transaction->counterpartName}\n";
            echo "---\n";
            
            $allTransactions[] = $transaction;
        }
        
        // Get next page if available
        if ($pagination->hasNext()) {
            $pagination = $transactionService->list([
                'page[limit]' => 20,
                'page[after]' => $pagination->getNextCursor(),
            ]);
        } else {
            break;
        }
        
    } while (true);
    
    echo "Total transactions retrieved: " . count($allTransactions) . "\n";
    
} catch (ApiException $e) {
    echo "Failed to fetch transactions: {$e->getMessage()}\n";
}
```

---

### 4. Create a Payment with Idempotency

```php
<?php

use AlchemicStudio\Ponto\Client;
use AlchemicStudio\Ponto\Utils\IdempotencyKey;
use AlchemicStudio\Ponto\Exceptions\ValidationException;
use AlchemicStudio\Ponto\Exceptions\ApiException;

$ponto = new Client($config);
$accountId = '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b';

try {
    $paymentService = $ponto->payments($accountId);
    
    // Generate idempotency key for safe retry
    $idempotencyKey = IdempotencyKey::generate();
    
    $paymentData = [
        'currency' => 'EUR',
        'amount' => 59.00,
        'creditorName' => 'Alex Creditor',
        'creditorAccountReference' => 'BE55732022998044',
        'creditorAccountReferenceType' => 'IBAN',
        'creditorAgent' => 'NBBEBEBB203',
        'creditorAgentType' => 'BIC',
        'remittanceInformation' => 'Invoice payment #12345',
        'remittanceInformationType' => 'unstructured',
        'endToEndId' => '1234567890',
        'requestedExecutionDate' => '2025-12-31',
        'redirectUri' => 'https://your-app.com/payment-confirmation',
    ];
    
    $payment = $paymentService->create($paymentData, $idempotencyKey);
    
    echo "Payment created successfully!\n";
    echo "Payment ID: {$payment->id}\n";
    echo "Status: {$payment->status}\n";
    echo "Redirect user to: {$payment->redirectUrl}\n";
    
    // Store payment ID for later retrieval
    // $_SESSION['payment_id'] = $payment->id;
    
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "Payment creation failed: {$e->getMessage()}\n";
    if ($e->errors) {
        print_r($e->errors);
    }
}
```

---

### 5. Create Bulk Payment

```php
<?php

use AlchemicStudio\Ponto\Client;
use AlchemicStudio\Ponto\Utils\IdempotencyKey;

$ponto = new Client($config);
$accountId = '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b';

try {
    $paymentService = $ponto->payments($accountId);
    
    $bulkPaymentData = [
        'reference' => 'Invoice Payments - Batch 2025-10',
        'redirectUri' => 'https://your-app.com/bulk-payment-confirmation',
        'requestedExecutionDate' => '2025-11-01',
        'batchBookingPreferred' => true,
        'payments' => [
            [
                'currency' => 'EUR',
                'amount' => 150.00,
                'creditorName' => 'Supplier A',
                'creditorAccountReference' => 'BE55732022998044',
                'creditorAccountReferenceType' => 'IBAN',
                'remittanceInformation' => 'Invoice #A-001',
                'remittanceInformationType' => 'unstructured',
            ],
            [
                'currency' => 'EUR',
                'amount' => 250.50,
                'creditorName' => 'Supplier B',
                'creditorAccountReference' => 'BE73055155935764',
                'creditorAccountReferenceType' => 'IBAN',
                'remittanceInformation' => 'Invoice #B-042',
                'remittanceInformationType' => 'unstructured',
            ],
            // Add more payments...
        ],
    ];
    
    $bulkPayment = $paymentService->createBulk(
        $bulkPaymentData,
        IdempotencyKey::generate()
    );
    
    echo "Bulk payment created: {$bulkPayment->id}\n";
    echo "Redirect to: {$bulkPayment->redirectUrl}\n";
    
} catch (ApiException $e) {
    echo "Bulk payment failed: {$e->getMessage()}\n";
}
```

---

### 6. Synchronize Account Data

```php
<?php

use AlchemicStudio\Ponto\Client;
use AlchemicStudio\Ponto\Exceptions\ApiException;

$ponto = new Client($config);
$accountId = '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b';

try {
    $syncService = $ponto->synchronizations();
    
    // Create synchronization request
    $sync = $syncService->create([
        'resourceType' => 'account',
        'resourceId' => $accountId,
        'subtype' => 'accountTransactions',
    ]);
    
    echo "Synchronization started: {$sync->id}\n";
    echo "Status: {$sync->status}\n";
    
    // Poll synchronization status
    $maxAttempts = 30;
    $attempt = 0;
    
    while ($attempt < $maxAttempts) {
        sleep(2); // Wait 2 seconds
        
        $sync = $syncService->get($sync->id);
        echo "Status: {$sync->status}\n";
        
        if ($sync->status === 'success') {
            echo "Synchronization completed successfully!\n";
            
            // Now fetch updated transactions
            $transactionService = $ponto->transactions($accountId);
            $transactions = $transactionService->list(['page[limit]' => 50]);
            
            echo "Retrieved {$transactions->count()} transactions\n";
            break;
        }
        
        if ($sync->status === 'failed') {
            echo "Synchronization failed!\n";
            if (!empty($sync->errors)) {
                foreach ($sync->errors as $error) {
                    echo "Error: {$error['code']} - {$error['detail']}\n";
                }
            }
            break;
        }
        
        $attempt++;
    }
    
    if ($attempt >= $maxAttempts) {
        echo "Synchronization timeout\n";
    }
    
} catch (ApiException $e) {
    echo "Synchronization error: {$e->getMessage()}\n";
}
```

---

### 7. Error Handling with Retry Strategy

```php
<?php

use AlchemicStudio\Ponto\Client;
use AlchemicStudio\Ponto\Exceptions\ApiException;
use AlchemicStudio\Ponto\Exceptions\NetworkException;
use AlchemicStudio\Ponto\Exceptions\AuthenticationException;

function fetchTransactionsWithRetry(Client $ponto, string $accountId, int $maxRetries = 3): array
{
    $attempt = 0;
    $backoffMs = 1000; // Start with 1 second
    
    while ($attempt < $maxRetries) {
        try {
            $transactionService = $ponto->transactions($accountId);
            $pagination = $transactionService->list(['page[limit]' => 50]);
            
            return $pagination->items();
            
        } catch (NetworkException $e) {
            $attempt++;
            if ($attempt >= $maxRetries) {
                throw $e;
            }
            
            echo "Network error, retrying in " . ($backoffMs / 1000) . "s... (attempt {$attempt}/{$maxRetries})\n";
            usleep($backoffMs * 1000);
            $backoffMs *= 2; // Exponential backoff
            
        } catch (ApiException $e) {
            // Check if error is retryable
            if (in_array($e->statusCode, [429, 500, 502, 503, 504])) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                
                // For 429 (rate limit), check Retry-After header if available
                $retryAfter = $e->context['retry_after'] ?? ($backoffMs / 1000);
                
                echo "API error {$e->statusCode}, retrying after {$retryAfter}s...\n";
                sleep((int) $retryAfter);
                $backoffMs *= 2;
            } else {
                // Non-retryable error
                throw $e;
            }
            
        } catch (AuthenticationException $e) {
            // Token might be expired, let AuthProvider handle refresh
            echo "Authentication error: {$e->getMessage()}\n";
            throw $e;
        }
    }
    
    throw new \RuntimeException('Max retries exceeded');
}

// Usage
try {
    $ponto = new Client($config);
    $accountId = '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b';
    
    $transactions = fetchTransactionsWithRetry($ponto, $accountId);
    echo "Successfully retrieved " . count($transactions) . " transactions\n";
    
} catch (\Exception $e) {
    echo "Failed after all retries: {$e->getMessage()}\n";
}
```

---

### 8. Complete Example Files

#### `src/Client.php`

```php
<?php

declare(strict_types=1);

namespace AlchemicStudio\Ponto;

use AlchemicStudio\Ponto\Auth\AuthProvider;
use AlchemicStudio\Ponto\Auth\TokenStorage;
use AlchemicStudio\Ponto\Http\HttpClient;
use AlchemicStudio\Ponto\Http\RequestSigner;
use AlchemicStudio\Ponto\Services\AccountService;
use AlchemicStudio\Ponto\Services\TransactionService;
use AlchemicStudio\Ponto\Services\PaymentService;
use AlchemicStudio\Ponto\Services\SynchronizationService;
use AlchemicStudio\Ponto\Exceptions\ValidationException;

final class Client
{
    private HttpClient $httpClient;
    private AuthProvider $authProvider;
    private array $config;

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->config = $config;
        
        // Initialize token storage
        $tokenStorage = new TokenStorage($config['token_storage_path'] ?? sys_get_temp_dir() . '/ponto_tokens.json');
        
        // Initialize auth provider
        $this->authProvider = new AuthProvider(
            clientId: $config['client_id'],
            clientSecret: $config['client_secret'],
            baseUrl: $config['base_url'],
            tokenStorage: $tokenStorage
        );
        
        // Initialize request signer for payments (production only)
        $signer = null;
        if (isset($config['signature_cert_id']) && isset($config['signature_key_path'])) {
            $signer = new RequestSigner(
                certificateId: $config['signature_cert_id'],
                privateKeyPath: $config['signature_key_path']
            );
        }
        
        // Initialize HTTP client
        $this->httpClient = new HttpClient(
            baseUrl: $config['base_url'],
            certPath: $config['cert_path'],
            keyPath: $config['key_path'],
            authProvider: $this->authProvider,
            signer: $signer
        );
    }

    private function validateConfig(array $config): void
    {
        $required = ['client_id', 'client_secret', 'base_url', 'cert_path', 'key_path'];
        
        foreach ($required as $key) {
            if (empty($config[$key])) {
                throw new ValidationException("Configuration key '{$key}' is required");
            }
        }
        
        if (!file_exists($config['cert_path'])) {
            throw new ValidationException("Certificate file not found: {$config['cert_path']}");
        }
        
        if (!file_exists($config['key_path'])) {
            throw new ValidationException("Private key file not found: {$config['key_path']}");
        }
    }

    public function getAuthProvider(): AuthProvider
    {
        return $this->authProvider;
    }

    public function accounts(): AccountService
    {
        return new AccountService($this->httpClient);
    }

    public function transactions(string $accountId): TransactionService
    {
        return new TransactionService($this->httpClient, $accountId);
    }

    public function payments(string $accountId): PaymentService
    {
        return new PaymentService($this->httpClient, $accountId);
    }

    public function synchronizations(): SynchronizationService
    {
        return new SynchronizationService($this->httpClient);
    }
}
```

---

#### `src/Services/TransactionService.php`

```php
<?php

declare(strict_types=1);

namespace AlchemicStudio\Ponto\Services;

use AlchemicStudio\Ponto\Http\HttpClient;
use AlchemicStudio\Ponto\Models\Transaction;
use AlchemicStudio\Ponto\Utils\Pagination;
use AlchemicStudio\Ponto\Exceptions\ApiException;

final class TransactionService
{
    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly string $accountId
    ) {}

    public function list(array $filters = []): Pagination
    {
        $response = $this->httpClient->get(
            "/accounts/{$this->accountId}/transactions",
            $filters
        );
        
        $data = $response->json();
        
        $transactions = array_map(
            fn(array $item) => Transaction::fromArray($item),
            $data['data'] ?? []
        );
        
        return new Pagination(
            items: $transactions,
            meta: $data['meta'] ?? [],
            links: $data['links'] ?? []
        );
    }

    public function get(string $transactionId): Transaction
    {
        $response = $this->httpClient->get(
            "/accounts/{$this->accountId}/transactions/{$transactionId}"
        );
        
        $data = $response->json();
        
        return Transaction::fromArray($data['data']);
    }

    public function listUpdated(string $synchronizationId, array $filters = []): Pagination
    {
        $response = $this->httpClient->get(
            "/synchronizations/{$synchronizationId}/updated-transactions",
            $filters
        );
        
        $data = $response->json();
        
        $transactions = array_map(
            fn(array $item) => Transaction::fromArray($item),
            $data['data'] ?? []
        );
        
        return new Pagination(
            items: $transactions,
            meta: $data['meta'] ?? [],
            links: $data['links'] ?? []
        );
    }
}
```

---

#### `src/Services/PaymentService.php`

```php
<?php

declare(strict_types=1);

namespace AlchemicStudio\Ponto\Services;

use AlchemicStudio\Ponto\Http\HttpClient;
use AlchemicStudio\Ponto\Models\Payment;
use AlchemicStudio\Ponto\Models\BulkPayment;
use AlchemicStudio\Ponto\Exceptions\ValidationException;
use AlchemicStudio\Ponto\Exceptions\ApiException;

final class PaymentService
{
    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly string $accountId
    ) {}

    public function create(array $paymentData, ?string $idempotencyKey = null): Payment
    {
        $this->validatePaymentData($paymentData);
        
        $payload = [
            'data' => [
                'type' => 'payment',
                'attributes' => $paymentData,
            ],
        ];
        
        $response = $this->httpClient->post(
            "/accounts/{$this->accountId}/payments",
            $payload,
            $idempotencyKey
        );
        
        $data = $response->json();
        
        return Payment::fromArray($data['data']);
    }

    public function get(string $paymentId): Payment
    {
        $response = $this->httpClient->get(
            "/accounts/{$this->accountId}/payments/{$paymentId}"
        );
        
        $data = $response->json();
        
        return Payment::fromArray($data['data']);
    }

    public function delete(string $paymentId): void
    {
        $this->httpClient->delete(
            "/accounts/{$this->accountId}/payments/{$paymentId}"
        );
    }

    public function createBulk(array $bulkPaymentData, ?string $idempotencyKey = null): BulkPayment
    {
        $this->validateBulkPaymentData($bulkPaymentData);
        
        $payload = [
            'data' => [
                'type' => 'bulkPayment',
                'attributes' => $bulkPaymentData,
            ],
        ];
        
        $response = $this->httpClient->post(
            "/accounts/{$this->accountId}/bulk-payments",
            $payload,
            $idempotencyKey
        );
        
        $data = $response->json();
        
        return BulkPayment::fromArray($data['data']);
    }

    private function validatePaymentData(array $data): void
    {
        $required = ['currency', 'amount', 'creditorName', 'creditorAccountReference', 'creditorAccountReferenceType'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new ValidationException("Payment field '{$field}' is required");
            }
        }
        
        if ($data['currency'] !== 'EUR') {
            throw new ValidationException("Only EUR currency is currently supported");
        }
        
        if ($data['amount'] <= 0) {
            throw new ValidationException("Amount must be positive");
        }
    }

    private function validateBulkPaymentData(array $data): void
    {
        if (empty($data['reference'])) {
            throw new ValidationException("Bulk payment 'reference' is required");
        }
        
        if (empty($data['payments']) || !is_array($data['payments'])) {
            throw new ValidationException("Bulk payment 'payments' array is required");
        }
        
        foreach ($data['payments'] as $index => $payment) {
            try {
                $this->validatePaymentData($payment);
            } catch (ValidationException $e) {
                throw new ValidationException("Payment at index {$index}: {$e->getMessage()}");
            }
        }
    }
}
```

---

#### `src/Models/Transaction.php`

```php
<?php

declare(strict_types=1);

namespace AlchemicStudio\Ponto\Models;

final readonly class Transaction
{
    public function __construct(
        public string $id,
        public float $amount,
        public string $currency,
        public string $description,
        public \DateTimeImmutable $executionDate,
        public \DateTimeImmutable $valueDate,
        public ?string $remittanceInformation,
        public ?string $remittanceInformationType,
        public ?string $counterpartName,
        public ?string $counterpartReference,
        public ?string $endToEndId,
        public string $digest,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        public ?string $additionalInformation = null,
        public ?string $bankTransactionCode = null,
        public ?string $creditorId = null,
        public ?string $mandateId = null,
        public ?string $purposeCode = null,
        public ?string $proprietaryBankTransactionCode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $attributes = $data['attributes'] ?? [];
        
        return new self(
            id: $data['id'],
            amount: (float) $attributes['amount'],
            currency: $attributes['currency'],
            description: $attributes['description'] ?? '',
            executionDate: new \DateTimeImmutable($attributes['executionDate']),
            valueDate: new \DateTimeImmutable($attributes['valueDate']),
            remittanceInformation: $attributes['remittanceInformation'] ?? null,
            remittanceInformationType: $attributes['remittanceInformationType'] ?? null,
            counterpartName: $attributes['counterpartName'] ?? null,
            counterpartReference: $attributes['counterpartReference'] ?? null,
            endToEndId: $attributes['endToEndId'] ?? null,
            digest: $attributes['digest'],
            createdAt: new \DateTimeImmutable($attributes['createdAt']),
            updatedAt: new \DateTimeImmutable($attributes['updatedAt']),
            additionalInformation: $attributes['additionalInformation'] ?? null,
            bankTransactionCode: $attributes['bankTransactionCode'] ?? null,
            creditorId: $attributes['creditorId'] ?? null,
            mandateId: $attributes['mandateId'] ?? null,
            purposeCode: $attributes['purposeCode'] ?? null,
            proprietaryBankTransactionCode: $attributes['proprietaryBankTransactionCode'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => 'transaction',
            'attributes' => [
                'amount' => $this->amount,
                'currency' => $this->currency,
                'description' => $this->description,
                'executionDate' => $this->executionDate->format('c'),
                'valueDate' => $this->valueDate->format('c'),
                'remittanceInformation' => $this->remittanceInformation,
                'remittanceInformationType' => $this->remittanceInformationType,
                'counterpartName' => $this->counterpartName,
                'counterpartReference' => $this->counterpartReference,
                'endToEndId' => $this->endToEndId,
                'digest' => $this->digest,
                'createdAt' => $this->createdAt->format('c'),
                'updatedAt' => $this->updatedAt->format('c'),
            ],
        ];
    }
}
```

---

## Configuration

### Environment Variables

Store sensitive configuration in environment variables:

```bash
# .env file
PONTO_CLIENT_ID=your-client-id-uuid
PONTO_CLIENT_SECRET=your-client-secret
PONTO_BASE_URL=https://api.ibanity.com/ponto-connect
PONTO_CERT_PATH=/path/to/certificate.pem
PONTO_KEY_PATH=/path/to/private_key.pem
PONTO_SIGNATURE_CERT_ID=your-signature-cert-uuid
PONTO_SIGNATURE_KEY_PATH=/path/to/signature_private_key.pem
PONTO_TOKEN_STORAGE_PATH=/secure/path/to/tokens.json

# Sandbox environment
# PONTO_BASE_URL=https://api.acceptance.ibanity.com/ponto-connect
```

### Configuration Array

```php
<?php

$config = [
    'client_id' => getenv('PONTO_CLIENT_ID'),
    'client_secret' => getenv('PONTO_CLIENT_SECRET'),
    'base_url' => getenv('PONTO_BASE_URL'),
    'cert_path' => getenv('PONTO_CERT_PATH'),
    'key_path' => getenv('PONTO_KEY_PATH'),
    'signature_cert_id' => getenv('PONTO_SIGNATURE_CERT_ID'),      // Production only
    'signature_key_path' => getenv('PONTO_SIGNATURE_KEY_PATH'),    // Production only
    'token_storage_path' => getenv('PONTO_TOKEN_STORAGE_PATH'),
];
```

### Environment Detection

```php
<?php

function isPontoProduction(string $baseUrl): bool
{
    return str_contains($baseUrl, 'api.ibanity.com');
}

$isProduction = isPontoProduction($config['base_url']);

if ($isProduction && empty($config['signature_cert_id'])) {
    throw new \RuntimeException('Signature certificate is required for production');
}
```

---

## Authentication Strategy

### OAuth2 Flow with PKCE

The Ponto Connect API uses OAuth2 authorization code flow with PKCE (Proof Key for Code Exchange) for enhanced security.

#### Flow Steps

1. **Generate PKCE Parameters**
   ```php
   $codeVerifier = bin2hex(random_bytes(32));
   $codeChallenge = rtrim(
       strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'),
       '='
   );
   ```

2. **Redirect User to Authorization URL**
   ```php
   $authUrl = $ponto->getAuthProvider()->getAuthorizationUrl(
       redirectUri: 'https://your-app.com/callback',
       codeChallenge: $codeChallenge,
       scopes: ['ai', 'pi', 'name', 'offline_access']
   );
   
   // Redirect user
   header("Location: {$authUrl}");
   exit;
   ```

3. **Handle Callback & Exchange Code**
   ```php
   // In your callback handler
   $code = $_GET['code'] ?? null;
   
   if (!$code) {
       throw new \Exception('Authorization code not received');
   }
   
   $tokens = $ponto->getAuthProvider()->exchangeAuthorizationCode(
       code: $code,
       codeVerifier: $codeVerifier, // Must match the one used to generate challenge
       redirectUri: 'https://your-app.com/callback'
   );
   
   // Tokens are automatically stored by TokenStorage
   ```

### Token Management

#### Access Token Lifecycle

- **Lifetime:** 1800 seconds (30 minutes)
- **Type:** Bearer token
- **Usage:** Include in `Authorization: Bearer {access_token}` header
- **Auto-refresh:** Handled transparently by `AuthProvider`

#### Refresh Token Behavior

[ASSUMPTION: Based on API documentation analysis]

- **Single-use:** Each refresh token can only be used once
- **New token provided:** Each token refresh returns a new refresh_token
- **Storage:** Must be securely persisted between requests
- **Revocation:** Can be revoked via API call

### Scopes

- **ai** (Account Information): Access account and transaction data
- **pi** (Payment Initiation): Initiate payments and bulk payments
- **name**: Access organization name via userInfo endpoint
- **offline_access**: Receive refresh token for offline access

---

## Security Best Practices

### 1. Certificate Management

**DO:**
- Store certificates outside web root
- Use restrictive file permissions (600 for private keys)
- Rotate certificates regularly
- Use separate certificates for sandbox and production

**DON'T:**
- Commit certificates to version control
- Share certificates across environments
- Use sandbox certificates in production

```bash
# Set proper permissions
chmod 600 /path/to/private_key.pem
chmod 644 /path/to/certificate.pem
```

### 2. Credential Storage

**DO:**
- Use environment variables or secure vaults (e.g., AWS Secrets Manager, HashiCorp Vault)
- Encrypt sensitive data at rest
- Use secure token storage with file permissions 600

**DON'T:**
- Hardcode credentials in source code
- Store credentials in configuration files committed to VCS
- Log sensitive data (tokens, secrets, full request bodies)

### 3. TLS/SSL Configuration

**Always use TLS 1.2 or higher:**

```php
<?php

use GuzzleHttp\Client;

$client = new Client([
    'verify' => true,  // Enable SSL certificate verification
    'cert' => ['/path/to/certificate.pem', 'password'],
    'ssl_key' => ['/path/to/private_key.pem', 'password'],
    'http_errors' => false,
    'timeout' => 30,
    'connect_timeout' => 10,
]);
```

### 4. Idempotency Key Usage

**Always use idempotency keys for:**
- Payment creation (single and bulk)
- Synchronization requests
- Any POST/PATCH operation that shouldn't be duplicated

```php
<?php

use AlchemicStudio\Ponto\Utils\IdempotencyKey;

// Generate once per operation attempt
$idempotencyKey = IdempotencyKey::generate();

// Store it if you need to retry
$_SESSION['payment_idempotency_key'] = $idempotencyKey;

// Use same key for retries
$payment = $paymentService->create($paymentData, $idempotencyKey);
```

### 5. Input Validation

```php
<?php

class PaymentService
{
    private function validatePaymentData(array $data): void
    {
        // Validate IBAN format
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $data['creditorAccountReference'] ?? '')) {
            throw new ValidationException('Invalid IBAN format');
        }
        
        // Validate BIC format (if provided)
        if (isset($data['creditorAgent']) && 
            !preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $data['creditorAgent'])) {
            throw new ValidationException('Invalid BIC format');
        }
        
        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new ValidationException('Amount must be positive');
        }
    }
}
```

---

## Testing

### Framework: Pest v4.1

The project uses Pest with functional syntax for testing.

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
./vendor/bin/pest tests/Unit/TransactionServiceTest.php

# Run with coverage
composer test-coverage

# Run with verbose output
./vendor/bin/pest --verbose
```

### Unit Test Example

#### `tests/Unit/TransactionServiceTest.php`

```php
<?php

use AlchemicStudio\Ponto\Services\TransactionService;
use AlchemicStudio\Ponto\Http\HttpClient;
use AlchemicStudio\Ponto\Http\Response;
use AlchemicStudio\Ponto\Models\Transaction;
use AlchemicStudio\Ponto\Utils\Pagination;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->accountId = '5a8820b5-87c5-4d8d-b0f1-32e710b0bd9b';
    $this->service = new TransactionService($this->httpClient, $this->accountId);
});

afterEach(function () {
    Mockery::close();
});

test('can list transactions', function () {
    $responseData = [
        'data' => [
            [
                'id' => 'transaction-1',
                'type' => 'transaction',
                'attributes' => [
                    'amount' => -50.00,
                    'currency' => 'EUR',
                    'description' => 'Test transaction',
                    'executionDate' => '2025-10-01T10:00:00Z',
                    'valueDate' => '2025-10-01T10:00:00Z',
                    'digest' => 'abc123',
                    'createdAt' => '2025-10-01T10:00:00Z',
                    'updatedAt' => '2025-10-01T10:00:00Z',
                ],
            ],
        ],
        'meta' => ['paging' => ['limit' => 10]],
        'links' => [],
    ];
    
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('json')->andReturn($responseData);
    
    $this->httpClient
        ->shouldReceive('get')
        ->with("/accounts/{$this->accountId}/transactions", ['page[limit]' => 10])
        ->once()
        ->andReturn($response);
    
    $result = $this->service->list(['page[limit]' => 10]);
    
    expect($result)->toBeInstanceOf(Pagination::class);
    expect($result->items())->toHaveCount(1);
    expect($result->items()[0])->toBeInstanceOf(Transaction::class);
    expect($result->items()[0]->id)->toBe('transaction-1');
    expect($result->items()[0]->amount)->toBe(-50.00);
});

test('can get single transaction', function () {
    $transactionId = 'transaction-1';
    $responseData = [
        'data' => [
            'id' => $transactionId,
            'type' => 'transaction',
            'attributes' => [
                'amount' => -50.00,
                'currency' => 'EUR',
                'description' => 'Test transaction',
                'executionDate' => '2025-10-01T10:00:00Z',
                'valueDate' => '2025-10-01T10:00:00Z',
                'digest' => 'abc123',
                'createdAt' => '2025-10-01T10:00:00Z',
                'updatedAt' => '2025-10-01T10:00:00Z',
            ],
        ],
    ];
    
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('json')->andReturn($responseData);
    
    $this->httpClient
        ->shouldReceive('get')
        ->with("/accounts/{$this->accountId}/transactions/{$transactionId}")
        ->once()
        ->andReturn($response);
    
    $result = $this->service->get($transactionId);
    
    expect($result)->toBeInstanceOf(Transaction::class);
    expect($result->id)->toBe($transactionId);
    expect($result->amount)->toBe(-50.00);
    expect($result->currency)->toBe('EUR');
});

test('throws exception on API error', function () {
    $this->httpClient
        ->shouldReceive('get')
        ->andThrow(new \AlchemicStudio\Ponto\Exceptions\ApiException(
            'API Error',
            500,
            ['error' => 'Internal Server Error'],
            'req-123'
        ));
    
    $this->service->list();
})->throws(\AlchemicStudio\Ponto\Exceptions\ApiException::class);
```

### Integration Test Example

#### `tests/Integration/AuthenticationFlowTest.php`

```php
<?php

use AlchemicStudio\Ponto\Client;

test('can generate authorization URL', function () {
    $config = [
        'client_id' => 'test-client-id',
        'client_secret' => 'test-secret',
        'base_url' => 'https://api.acceptance.ibanity.com/ponto-connect',
        'cert_path' => __DIR__ . '/../fixtures/test-cert.pem',
        'key_path' => __DIR__ . '/../fixtures/test-key.pem',
    ];
    
    $client = new Client($config);
    $authProvider = $client->getAuthProvider();
    
    $codeChallenge = 'test-challenge';
    $redirectUri = 'https://example.com/callback';
    
    $url = $authProvider->getAuthorizationUrl($redirectUri, $codeChallenge);
    
    expect($url)->toContain('authorize.acceptance.myponto.net');
    expect($url)->toContain('client_id=test-client-id');
    expect($url)->toContain('redirect_uri=' . urlencode($redirectUri));
    expect($url)->toContain('code_challenge=' . $codeChallenge);
    expect($url)->toContain('response_type=code');
})->skip(!file_exists(__DIR__ . '/../fixtures/test-cert.pem'), 'Test certificates not available');
```

---

## Framework Integration

### Laravel Integration

#### Service Provider

```php
<?php
// app/Providers/PontoServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use AlchemicStudio\Ponto\Client;

class PontoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client([
                'client_id' => config('ponto.client_id'),
                'client_secret' => config('ponto.client_secret'),
                'base_url' => config('ponto.base_url'),
                'cert_path' => config('ponto.cert_path'),
                'key_path' => config('ponto.key_path'),
                'signature_cert_id' => config('ponto.signature_cert_id'),
                'signature_key_path' => config('ponto.signature_key_path'),
                'token_storage_path' => storage_path('app/ponto-tokens.json'),
            ]);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/ponto.php' => config_path('ponto.php'),
        ], 'ponto-config');
    }
}
```

#### Configuration File

```php
<?php
// config/ponto.php

return [
    'client_id' => env('PONTO_CLIENT_ID'),
    'client_secret' => env('PONTO_CLIENT_SECRET'),
    'base_url' => env('PONTO_BASE_URL', 'https://api.ibanity.com/ponto-connect'),
    'cert_path' => env('PONTO_CERT_PATH'),
    'key_path' => env('PONTO_KEY_PATH'),
    'signature_cert_id' => env('PONTO_SIGNATURE_CERT_ID'),
    'signature_key_path' => env('PONTO_SIGNATURE_KEY_PATH'),
];
```

#### Usage in Laravel Controller

```php
<?php

namespace App\Http\Controllers;

use AlchemicStudio\Ponto\Client;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly Client $ponto
    ) {}
    
    public function index(Request $request, string $accountId)
    {
        try {
            $transactions = $this->ponto
                ->transactions($accountId)
                ->list(['page[limit]' => 50]);
            
            return view('transactions.index', [
                'transactions' => $transactions->items(),
                'pagination' => $transactions,
            ]);
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

---

## API Compliance

### Endpoints Covered

#### Account Information (ai scope)

- ✅ **GET /accounts** - List accounts with pagination
- ✅ **GET /accounts/{id}** - Get single account
- ✅ **DELETE /accounts/{id}** - Revoke account access
- ✅ **GET /accounts/{accountId}/transactions** - List transactions
- ✅ **GET /accounts/{accountId}/transactions/{id}** - Get single transaction
- ✅ **GET /accounts/{accountId}/pending-transactions** - List pending transactions
- ✅ **GET /financial-institutions** - List financial institutions
- ✅ **GET /financial-institutions/{id}** - Get financial institution

#### Payment Initiation (pi scope)

- ✅ **POST /accounts/{accountId}/payments** - Create payment
- ✅ **GET /accounts/{accountId}/payments/{id}** - Get payment
- ✅ **DELETE /accounts/{accountId}/payments/{id}** - Cancel payment
- ✅ **POST /accounts/{accountId}/bulk-payments** - Create bulk payment
- ✅ **GET /accounts/{accountId}/bulk-payments/{id}** - Get bulk payment
- ✅ **DELETE /accounts/{accountId}/bulk-payments/{id}** - Cancel bulk payment

#### Synchronization

- ✅ **POST /synchronizations** - Create synchronization
- ✅ **GET /synchronizations/{id}** - Get synchronization status
- ✅ **GET /synchronizations/{id}/updated-transactions** - List updated transactions
- ✅ **GET /synchronizations/{id}/updated-pending-transactions** - List updated pending transactions

#### Authorization

- ✅ **POST /oauth2/token** - Exchange authorization code
- ✅ **POST /oauth2/token** - Refresh access token
- ✅ **POST /oauth2/revoke** - Revoke refresh token

### Known Limitations

1. **Currency Support**: Currently only EUR is fully supported by most financial institutions.
2. **Synchronization Frequency**: Minimum 30 minutes between synchronizations for the same account/subtype.
3. **Payment Signature**: HTTP signature required for production payment requests (sandbox exempted).
4. **Batch Booking**: `batchBookingPreferred` for bulk payments depends on financial institution support.
5. **[ASSUMPTION]** Rate Limits: No explicit rate limits documented; implement exponential backoff for 429 responses.

---

## Versioning & CI

### Semantic Versioning

Follow [Semantic Versioning 2.0.0](https://semver.org/):

- **MAJOR**: Incompatible API changes
- **MINOR**: Backward-compatible functionality additions
- **PATCH**: Backward-compatible bug fixes

### CI/CD Pipeline Example

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        php: ['8.4']
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: curl, json, openssl, mbstring
          coverage: xdebug
      
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Run tests
        run: composer test
      
      - name: Run static analysis
        run: composer stan
      
      - name: Check code style
        run: composer format -- --dry-run --diff
```

---

## Summary

This documentation provides a complete guide for implementing and using the Ponto Connect PHP library. Key highlights:

- **Complete OAuth2 + PKCE authentication** with automatic token refresh
- **Full endpoint coverage** for accounts, transactions, payments, and synchronization
- **Production-ready** with TLS mutual authentication and HTTP signature support
- **Robust error handling** with retry strategies and exponential backoff
- **Type-safe** PHP 8.4+ implementation with readonly properties
- **Comprehensive testing** with Pest framework
- **Framework integration** examples (Laravel)
- **Security best practices** for credential management and logging

For questions or issues, please refer to:
- [Ponto Connect API Documentation](https://documentation.ibanity.com/ponto-connect/2/api/curl)
- [Ibanity Developer Portal](https://developer.ibanity.com/)

---

**Generated:** 2025-10-07  
**API Version:** Ponto Connect v2  
**Library Target:** PHP 8.4+

