<?php

namespace Paylink\Models;

use Illuminate\Support\Collection;

class PaylinkInvoiceResponse
{
    public PaylinkGatewayOrderRequest $gatewayOrderRequest;
    public float $amount;
    public string $transactionNo;
    public string $orderStatus;
    public $paymentErrors;
    public string $url;
    public string $qrUrl;
    public string $mobileUrl;
    public string $checkUrl;
    public bool $success;
    public bool $digitalOrder;
    public $foreignCurrencyRate;
    public $paymentReceipt;
    public $metadata;

    public function __construct(
        PaylinkGatewayOrderRequest $gatewayOrderRequest,
        float $amount,
        string $transactionNo,
        string $orderStatus,
        $paymentErrors,
        string $url,
        string $qrUrl,
        string $mobileUrl,
        string $checkUrl,
        bool $success,
        bool $digitalOrder,
        $foreignCurrencyRate,
        $paymentReceipt,
        $metadata
    ) {
        $this->gatewayOrderRequest = $gatewayOrderRequest;
        $this->amount = $amount;
        $this->transactionNo = $transactionNo;
        $this->orderStatus = $orderStatus;
        $this->paymentErrors = $paymentErrors;
        $this->url = $url;
        $this->qrUrl = $qrUrl;
        $this->mobileUrl = $mobileUrl;
        $this->checkUrl = $checkUrl;
        $this->success = $success;
        $this->digitalOrder = $digitalOrder;
        $this->foreignCurrencyRate = $foreignCurrencyRate;
        $this->paymentReceipt = $paymentReceipt;
        $this->metadata = $metadata;
    }

    public static function fromResponseData(array $data): self
    {
        $gatewayOrderRequest = PaylinkGatewayOrderRequest::fromArray($data['gatewayOrderRequest'] ?? []);

        return new self(
            $gatewayOrderRequest,
            $data['amount'] ?? 0.0,
            $data['transactionNo'] ?? '',
            $data['orderStatus'] ?? '',
            $data['paymentErrors'] ?? null,
            $data['url'] ?? '',
            $data['qrUrl'] ?? '',
            $data['mobileUrl'] ?? '',
            $data['checkUrl'] ?? '',
            $data['success'] ?? false,
            $data['digitalOrder'] ?? false,
            $data['foreignCurrencyRate'] ?? null,
            $data['paymentReceipt'] ?? null,
            $data['metadata'] ?? null
        );
    }
}
