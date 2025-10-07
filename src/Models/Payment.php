<?php

declare(strict_types=1);

namespace AlchemicStudio\PontoConnect\Models;

class Payment implements \JsonSerializable
{
    private string $id;

    private ?float $amount = null;

    private ?string $currency = null;

    private ?string $description = null;

    private ?string $creditorName = null;

    private ?string $creditorAccountReference = null;

    private ?string $creditorAccountReferenceType = null;

    private ?string $creditorAgent = null;

    private ?string $creditorAgentType = null;

    private ?string $status = null;

    private ?\DateTimeInterface $requestedExecutionDate = null;

    private ?string $endToEndId = null;

    private ?string $remittanceInformation = null;

    private ?string $remittanceInformationType = null;

    private ?string $redirectUrl = null;

    private array $rawData = [];

    private function __construct()
    {
    }

    public static function fromArray(array $data): self
    {
        $payment = new self();
        $payment->rawData = $data;
        $payment->id = $data['id'];

        $attributes = $data['attributes'] ?? [];

        $payment->amount = $attributes['amount'] ?? null;
        $payment->currency = $attributes['currency'] ?? null;
        $payment->description = $attributes['description'] ?? null;
        $payment->creditorName = $attributes['creditorName'] ?? null;
        $payment->creditorAccountReference = $attributes['creditorAccountReference'] ?? null;
        $payment->creditorAccountReferenceType = $attributes['creditorAccountReferenceType'] ?? null;
        $payment->creditorAgent = $attributes['creditorAgent'] ?? null;
        $payment->creditorAgentType = $attributes['creditorAgentType'] ?? null;
        $payment->status = $attributes['status'] ?? null;
        $payment->endToEndId = $attributes['endToEndId'] ?? null;
        $payment->remittanceInformation = $attributes['remittanceInformation'] ?? null;
        $payment->remittanceInformationType = $attributes['remittanceInformationType'] ?? null;

        if (isset($attributes['requestedExecutionDate'])) {
            $payment->requestedExecutionDate = new \DateTimeImmutable($attributes['requestedExecutionDate']);
        }

        // Parse redirect URL from links
        if (isset($data['links']['redirect'])) {
            $payment->redirectUrl = $data['links']['redirect'];
        }

        return $payment;
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

    public function getCreditorName(): ?string
    {
        return $this->creditorName;
    }

    public function getCreditorAccountReference(): ?string
    {
        return $this->creditorAccountReference;
    }

    public function getCreditorAccountReferenceType(): ?string
    {
        return $this->creditorAccountReferenceType;
    }

    public function getCreditorAgent(): ?string
    {
        return $this->creditorAgent;
    }

    public function getCreditorAgentType(): ?string
    {
        return $this->creditorAgentType;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getRequestedExecutionDate(): ?\DateTimeInterface
    {
        return $this->requestedExecutionDate;
    }

    public function getEndToEndId(): ?string
    {
        return $this->endToEndId;
    }

    public function getRemittanceInformation(): ?string
    {
        return $this->remittanceInformation;
    }

    public function getRemittanceInformationType(): ?string
    {
        return $this->remittanceInformationType;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'unsigned', 'accepted'], true);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'executed';
    }
}
