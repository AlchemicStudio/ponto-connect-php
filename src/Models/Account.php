<?php

declare(strict_types=1);

namespace AlchemicStudio\PontoConnect\Models;

class Account implements \JsonSerializable
{
    private string $id;

    private ?string $reference = null;

    private ?string $referenceType = null;

    private ?string $holderName = null;

    private ?string $currency = null;

    private ?float $availableBalance = null;

    private ?float $currentBalance = null;

    private ?string $description = null;

    private ?string $subtype = null;

    private ?string $product = null;

    private ?bool $deprecated = null;

    private ?\DateTimeInterface $authorizationExpirationExpectedAt = null;

    private ?\DateTimeInterface $authorizedAt = null;

    private ?string $internalReference = null;

    private ?string $financialInstitutionId = null;

    private ?\DateTimeInterface $synchronizedAt = null;

    private array $rawData = [];

    private function __construct()
    {
    }

    public static function fromArray(array $data): self
    {
        $account = new self();
        $account->rawData = $data;
        $account->id = $data['id'];

        $attributes = $data['attributes'] ?? [];

        $account->reference = $attributes['reference'] ?? null;
        $account->referenceType = $attributes['referenceType'] ?? null;
        $account->holderName = $attributes['holderName'] ?? null;
        $account->currency = $attributes['currency'] ?? null;
        $account->availableBalance = $attributes['availableBalance'] ?? null;
        $account->currentBalance = $attributes['currentBalance'] ?? null;
        $account->description = $attributes['description'] ?? null;
        $account->subtype = $attributes['subtype'] ?? null;
        $account->product = $attributes['product'] ?? null;
        $account->deprecated = $attributes['deprecated'] ?? null;
        $account->internalReference = $attributes['internalReference'] ?? null;

        if (isset($attributes['authorizationExpirationExpectedAt'])) {
            $account->authorizationExpirationExpectedAt = new \DateTimeImmutable($attributes['authorizationExpirationExpectedAt']);
        }

        if (isset($attributes['authorizedAt'])) {
            $account->authorizedAt = new \DateTimeImmutable($attributes['authorizedAt']);
        }

        // Parse relationships
        if (isset($data['relationships']['financialInstitution']['data']['id'])) {
            $account->financialInstitutionId = $data['relationships']['financialInstitution']['data']['id'];
        }

        // Parse metadata
        if (isset($data['meta']['synchronizedAt'])) {
            $account->synchronizedAt = new \DateTimeImmutable($data['meta']['synchronizedAt']);
        }

        return $account;
    }

    public function toArray(): array
    {
        return $this->rawData;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getReferenceType(): ?string
    {
        return $this->referenceType;
    }

    public function getHolderName(): ?string
    {
        return $this->holderName;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getAvailableBalance(): ?float
    {
        return $this->availableBalance;
    }

    public function getCurrentBalance(): ?float
    {
        return $this->currentBalance;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getSubtype(): ?string
    {
        return $this->subtype;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function isDeprecated(): ?bool
    {
        return $this->deprecated;
    }

    public function getAuthorizationExpirationExpectedAt(): ?\DateTimeInterface
    {
        return $this->authorizationExpirationExpectedAt;
    }

    public function getAuthorizedAt(): ?\DateTimeInterface
    {
        return $this->authorizedAt;
    }

    public function getInternalReference(): ?string
    {
        return $this->internalReference;
    }

    public function getFinancialInstitutionId(): ?string
    {
        return $this->financialInstitutionId;
    }

    public function getSynchronizedAt(): ?\DateTimeInterface
    {
        return $this->synchronizedAt;
    }
}
