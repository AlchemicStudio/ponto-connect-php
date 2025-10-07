<?php

declare(strict_types=1);

namespace AlchemicStudio\PontoConnect\Models;

class Transaction implements \JsonSerializable
{
    private string $id;

    private ?float $amount = null;

    private ?string $currency = null;

    private ?string $description = null;

    private ?string $remittanceInformation = null;

    private ?string $remittanceInformationType = null;

    private ?\DateTimeInterface $valueDate = null;

    private ?\DateTimeInterface $executionDate = null;

    private ?string $counterpartName = null;

    private ?string $counterpartReference = null;

    private ?string $bankTransactionCode = null;

    private ?string $proprietaryBankTransactionCode = null;

    private ?string $endToEndId = null;

    private ?string $mandateId = null;

    private ?string $creditorId = null;

    private ?string $purposeCode = null;

    private ?string $internalReference = null;

    private ?string $additionalInformation = null;

    private ?string $digest = null;

    private ?\DateTimeInterface $createdAt = null;

    private ?\DateTimeInterface $updatedAt = null;

    private array $rawData = [];

    private function __construct()
    {
    }

    public static function fromArray(array $data): self
    {
        $transaction = new self();
        $transaction->rawData = $data;
        $transaction->id = $data['id'];

        $attributes = $data['attributes'] ?? [];

        $transaction->amount = $attributes['amount'] ?? null;
        $transaction->currency = $attributes['currency'] ?? null;
        $transaction->description = $attributes['description'] ?? null;
        $transaction->remittanceInformation = $attributes['remittanceInformation'] ?? null;
        $transaction->remittanceInformationType = $attributes['remittanceInformationType'] ?? null;
        $transaction->counterpartName = $attributes['counterpartName'] ?? null;
        $transaction->counterpartReference = $attributes['counterpartReference'] ?? null;
        $transaction->bankTransactionCode = $attributes['bankTransactionCode'] ?? null;
        $transaction->proprietaryBankTransactionCode = $attributes['proprietaryBankTransactionCode'] ?? null;
        $transaction->endToEndId = $attributes['endToEndId'] ?? null;
        $transaction->mandateId = $attributes['mandateId'] ?? null;
        $transaction->creditorId = $attributes['creditorId'] ?? null;
        $transaction->purposeCode = $attributes['purposeCode'] ?? null;
        $transaction->internalReference = $attributes['internalReference'] ?? null;
        $transaction->additionalInformation = $attributes['additionalInformation'] ?? null;
        $transaction->digest = $attributes['digest'] ?? null;

        if (isset($attributes['valueDate'])) {
            $transaction->valueDate = new \DateTimeImmutable($attributes['valueDate']);
        }

        if (isset($attributes['executionDate'])) {
            $transaction->executionDate = new \DateTimeImmutable($attributes['executionDate']);
        }

        if (isset($attributes['createdAt'])) {
            $transaction->createdAt = new \DateTimeImmutable($attributes['createdAt']);
        }

        if (isset($attributes['updatedAt'])) {
            $transaction->updatedAt = new \DateTimeImmutable($attributes['updatedAt']);
        }

        return $transaction;
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getRemittanceInformation(): ?string
    {
        return $this->remittanceInformation;
    }

    public function getRemittanceInformationType(): ?string
    {
        return $this->remittanceInformationType;
    }

    public function getValueDate(): ?\DateTimeInterface
    {
        return $this->valueDate;
    }

    public function getExecutionDate(): ?\DateTimeInterface
    {
        return $this->executionDate;
    }

    public function getCounterpartName(): ?string
    {
        return $this->counterpartName;
    }

    public function getCounterpartReference(): ?string
    {
        return $this->counterpartReference;
    }

    public function getBankTransactionCode(): ?string
    {
        return $this->bankTransactionCode;
    }

    public function getProprietaryBankTransactionCode(): ?string
    {
        return $this->proprietaryBankTransactionCode;
    }

    public function getEndToEndId(): ?string
    {
        return $this->endToEndId;
    }

    public function getMandateId(): ?string
    {
        return $this->mandateId;
    }

    public function getCreditorId(): ?string
    {
        return $this->creditorId;
    }

    public function getPurposeCode(): ?string
    {
        return $this->purposeCode;
    }

    public function getInternalReference(): ?string
    {
        return $this->internalReference;
    }

    public function getAdditionalInformation(): ?string
    {
        return $this->additionalInformation;
    }

    public function getDigest(): ?string
    {
        return $this->digest;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isDebit(): bool
    {
        return $this->amount !== null && $this->amount < 0;
    }

    public function isCredit(): bool
    {
        return $this->amount !== null && $this->amount > 0;
    }
}
