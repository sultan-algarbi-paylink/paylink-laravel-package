<?php

namespace Paylink\Models;

class PaylinkGatewayOrderRequest
{
    public float $amount;
    public string $orderNumber;
    public string $callBackUrl;
    public string $clientEmail;
    public string $clientName;
    public string $clientMobile;
    public string $note;
    public string $cancelUrl;
    public array $products;
    public array $supportedCardBrands;
    public string $currency;
    public string $smsMessage;
    public bool $displayPending;
    public $receivers;
    public $partnerPortion;
    public $metadata;

    public function __construct(
        float $amount,
        string $orderNumber,
        string $callBackUrl,
        string $clientEmail,
        string $clientName,
        string $clientMobile,
        string $note,
        string $cancelUrl,
        array $products,
        array $supportedCardBrands,
        string $currency,
        string $smsMessage,
        bool $displayPending,
        $receivers,
        $partnerPortion,
        $metadata
    ) {
        $this->amount = $amount;
        $this->orderNumber = $orderNumber;
        $this->callBackUrl = $callBackUrl;
        $this->clientEmail = $clientEmail;
        $this->clientName = $clientName;
        $this->clientMobile = $clientMobile;
        $this->note = $note;
        $this->cancelUrl = $cancelUrl;
        $this->products = $products;
        $this->supportedCardBrands = $supportedCardBrands;
        $this->currency = $currency;
        $this->smsMessage = $smsMessage;
        $this->displayPending = $displayPending;
        $this->receivers = $receivers;
        $this->partnerPortion = $partnerPortion;
        $this->metadata = $metadata;
    }

    public static function fromArray(array $data): self
    {
        $products = array_map(
            fn($product) => PaylinkProduct::fromArray($product),
            $data['products'] ?? []
        );

        return new self(
            $data['amount'] ?? 0.0,
            $data['orderNumber'] ?? '',
            $data['callBackUrl'] ?? '',
            $data['clientEmail'] ?? '',
            $data['clientName'] ?? '',
            $data['clientMobile'] ?? '',
            $data['note'] ?? '',
            $data['cancelUrl'] ?? '',
            $products,
            $data['supportedCardBrands'] ?? [],
            $data['currency'] ?? '',
            $data['smsMessage'] ?? '',
            $data['displayPending'] ?? true,
            $data['receivers'] ?? null,
            $data['partnerPortion'] ?? null,
            $data['metadata'] ?? null
        );
    }
}
