<?php

namespace Paylink\Services;

use Illuminate\Support\Facades\Http;
use Paylink\Models\PaylinkProduct;

class MerchantService
{
    /**
     * All payment methods are accepted by Paylink
     * @see https://paylinksa.readme.io/docs/payment-methods
     */
    private const VALID_CARD_BRANDS = ['mada', 'visaMastercard', 'amex', 'tabby', 'tamara', 'stcpay', 'urpay'];

    /**
     * Paylink Service configration
     * @see https://paylinksa.readme.io/docs/authentication#request-body-parameters
     */
    private string $apiLink;
    private string $paymentPagePrefix;
    private string $appId;
    private string $secretKey;
    private bool $persistToken;
    private string $idToken;

    /**
     * Based on the current environment set the configration
     */
    public function __construct()
    {
        if (app()->environment('production')) {
            // links
            $this->apiLink = 'https://restapi.paylink.sa';
            $this->paymentPagePrefix = 'https://payment.paylink.sa/pay/order';

            // config
            $this->appId = config('paylink.merchant.production.app_id');
            $this->secretKey = config('paylink.merchant.production.secret_key');
            $this->persistToken = config('paylink.merchant.production.persist_token', false);
        } else {
            // links
            $this->apiLink = 'https://restpilot.paylink.sa';
            $this->paymentPagePrefix = 'https://paymentpilot.paylink.sa/pay/info';

            // config
            $this->appId = config('paylink.merchant.testing.app_id', 'APP_ID_1123453311');
            $this->secretKey = config('paylink.merchant.testing.secret_key', '0662abb5-13c7-38ab-cd12-236e58f43766');
            $this->persistToken = config('paylink.merchant.testing.persist_token', false);
        }
    }

    /** 
     * 
     * The first step when using Paylink API is authenticating and getting a token.
     * The merchant's system will use this token for every endpoint call to authenticate and authorize the merchant's system.
     * 
     * @see https://paylinksa.readme.io/docs/authentication
     */
    private function authentication()
    {
        // Request body parameters
        $requestBody = [
            'apiId' => $this->appId,
            'secretKey' => $this->secretKey,
            'persistToken' => $this->persistToken
        ];

        // endpoint
        $endpoint = $this->apiLink . '/api/auth';

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
        ])->post($endpoint, $requestBody);

        return $response;
    }

    private function _setIdToken()
    {
        $response = $this->authentication();
        if ($response->successful()) {
            // Decode the JSON response
            $responseData = $response->json();

            // Extract the token from the response
            $this->idToken = $responseData['id_token'];
        } else {
            $this->idToken = null;
        }
    }

    /**
     * Add invoice to the system:
     * allows merchants to generate invoices and receive payments online through the Paylink gateway.
     * The merchant will send the payment URL to the customer through different channels or redirect them to the Paylink payment page.
     *
     * @param float $amount The total amount of the invoice. NOTE: Buyer will pay this amount regardless of the total amounts of the products' prices.
     * @param string $clientMobile The mobile number of the client.
     * @param string $clientName The name of the client.
     * @param string $orderNumber A unique identifier for the invoice.
     * @param PaylinkProduct[] $products An array of PaylinkProduct objects to be included in the invoice.
     * @param string $callBackUrl Call back URL that will be called by the Paylink to the merchant system. This callback URL will receive two parameters: orderNumber, and transactionNo.
     * @param string|null $cancelUrl Call back URL to cancel orders that will be called by the Paylink to the merchant system. This callback URL will receive two parameters: orderNumber, and transactionNo.
     * @param string|null $clientEmail The email address of the client.
     * @param string|null $currency The currency code of the invoice. The default value is SAR. (e.g., USD, EUR, GBP).
     * @param string|null $note A note for the invoice.
     * @param string|null $smsMessage This option will enable the invoice to be sent to the client's mobile specified in clientMobile.
     * @param array|null $supportedCardBrands List of supported card brands. This list is optional. values are: [mada, visaMastercard, amex, tabby, tamara, stcpay, urpay]
     * @param bool|null $displayPending This option will make this invoice displayed in my.paylink.sa
     * 
     * @see https://paylinksa.readme.io/docs/invoices
     */

    public function addInvoice(
        float $amount,
        string $clientMobile,
        string $clientName,
        string $orderNumber,
        array $products,
        string $callBackUrl,
        ?string $cancelUrl = null,
        ?string $clientEmail = null,
        ?string $currency = null,
        ?string $note = null,
        ?string $smsMessage = null,
        ?array $supportedCardBrands = [],
        ?bool $displayPending = true
    ) {

        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // Filter and sanitize supportedCardBrands
        $filteredCardBrands = array_filter($supportedCardBrands, function ($brand) {
            return is_string($brand) && in_array($brand, self::VALID_CARD_BRANDS);
        });


        // Convert PaylinkProduct objects to arrays
        $productsArray = [];
        if (!empty($products)) {
            foreach ($products as $index => $product) {
                if ($product instanceof PaylinkProduct) {
                    $productsArray[] = $product->toArray();
                } else {
                    throw new \InvalidArgumentException("Invalid product type at index $index");
                }
            }
        }

        // endpoint
        $endpoint = $this->apiLink . '/api/addInvoice';

        // Request body parameters
        $requestBody = [
            'amount' => $amount,
            'callBackUrl' => $callBackUrl,
            'cancelUrl' => $cancelUrl,
            'clientEmail' => $clientEmail,
            'clientMobile' => $clientMobile,
            'currency' => $currency,
            'clientName' => $clientName,
            'note' => $note,
            'orderNumber' => $orderNumber,
            'products' => $productsArray,
            'smsMessage' => $smsMessage,
            'supportedCardBrands' => $filteredCardBrands,
            'displayPending' => $displayPending,
        ];

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
    }

    /**
     * Paylink Get Orders:
     * The merchant's application is responsible for calling this endpoint to check the payment status of the invoice using "TransactionNo." Then from the response,
     * the merchant's application checks the invoice payment status from the field "orderStatus."
     * 
     * @param string $transactionNo.
     * 
     * @see https://paylinksa.readme.io/docs/order-request
     */
    public function getInvoice(string $transactionNo)
    {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // endpoint
        $endpoint = $this->apiLink . '/api/getInvoice/' . $transactionNo;

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->get($endpoint);

        return $response;
    }

    /**
     * Paylink Cancel Orders:
     * enables the cancellation of an existing transaction initiated by a merchant using the Paylink payment gateway.
     * 
     * @param string $transactionNo.
     * 
     * @see https://paylinksa.readme.io/docs/cancel-invoice
     */
    public function cancelInvoice(string $transactionNo)
    {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // endpoint
        $endpoint = $this->apiLink . '/api/cancelInvoice';

        // Request body parameters
        $requestBody = [
            'transactionNo' => $transactionNo,
        ];

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
    }

    /**
     * Pay Invoices (Direct Integration):
     *          This specific endpoint is designed for merchants,
     *          enabling them to create invoices and accept online payments through the Paylink gateway.
     *          The integration process facilitates the collection of card information directly from customers.
     *          This information is then utilized for processing payments on your webpage.
     *          Notably, this method provides immediate results regarding the payment status, eliminating the need for webpage redirections.
     *
     * @param float $amount The total amount of the invoice. NOTE: Buyer will pay this amount regardless of the total amounts of the products' prices.
     * @param string $clientMobile The mobile number of the client.
     * @param string $clientName The name of the client.
     * @param string $orderNumber A unique identifier for the invoice.
     * @param PaylinkProduct[] $products An array of PaylinkProduct objects to be included in the invoice.
     * @param string $callBackUrl The URL will be called when the payment is complete or canceled.
     * @param string|null $clientEmail The email address of the client.
     * @param string|null $currency The currency code of the invoice. The default value is SAR. (e.g., USD, EUR, GBP).
     * @param string|null $note A note for the invoice.
     * @param string|null $smsMessage This option will enable the invoice to be sent to the client's mobile specified in clientMobile.
     * @param array|null $supportedCardBrands List of supported card brands. This list is optional. values are: [mada, visaMastercard, amex, tabby, tamara, stcpay, urpay]
     * @param bool|null $displayPending This option will make this invoice displayed in my.paylink.sa
     * @param string $cardNumber a sequence of digits unique to each card. In this case, the number provided is "4111111111111111", which is a commonly used placeholder in payment processing examples.
     * @param string $cardSecurityCode known as the CVV (Card Verification Value), this is a security feature for card-not-present transactions. Here, it is given as "446".
     * @param string $cardExpiryMonth should be Double digit month, including leading zero (MM format), example "04"
     * @param string $cardExpiryYear should be two digit year (yy format), example "24"
     * 
     * @see https://paylinksa.readme.io/docs/add-invoices-direct
     */
    public function processPaymentWithCardInfo(
        float $amount,
        string $clientMobile,
        string $clientName,
        string $orderNumber,
        array $products,
        string $cardNumber,
        string $cardSecurityCode,
        string $cardExpiryMonth,
        string $cardExpiryYear,
        string $callBackUrl,
        ?string $cancelUrl = null,
        ?string $clientEmail = null,
        ?string $currency = null,
        ?string $note = null,
        ?string $smsMessage = null,
        ?array $supportedCardBrands = [],
        ?bool $displayPending = true,
    ) {

        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // Filter and sanitize supportedCardBrands
        $filteredCardBrands = array_filter($supportedCardBrands, function ($brand) {
            return is_string($brand) && in_array($brand, self::VALID_CARD_BRANDS);
        });

        // Convert PaylinkProduct objects to arrays
        $productsArray = [];
        if (empty($products)) {
            foreach ($products as $product) {
                if ($product instanceof PaylinkProduct) {
                    $productsArray[] = $product->toArray();
                }
            }
        }

        // endpoint
        $endpoint = $this->apiLink . '/api/payInvoice';

        // Request body parameters
        $requestBody = [
            'amount' => $amount,
            'callBackUrl' => $callBackUrl,
            'cancelUrl' => $cancelUrl,
            'clientEmail' => $clientEmail,
            'clientMobile' => $clientMobile,
            'currency' => $currency,
            'clientName' => $clientName,
            'note' => $note,
            'orderNumber' => $orderNumber,
            'products' => $productsArray,
            'smsMessage' => $smsMessage,
            'supportedCardBrands' => $filteredCardBrands,
            'displayPending' => $displayPending,
            'card' => [
                'expiry' => [
                    'month' => $cardExpiryMonth,
                    'year' => $cardExpiryYear,
                ],
                'number' => $cardNumber,
                'securityCode' => $cardSecurityCode,
            ]
        ];

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
    }

    /**
     * Recurring Payment to the system:
     * enabling you to initiate or cancel recurring payments and to view all active regular payments
     *
     * @param float $paymentValue
     * @param string|null $currencyCode
     * @param string|null $paymentNote
     * @param string $customerName
     * @param string $customerMobile
     * @param string|null $customerEmail
     * @param string $callbackUrl
     * @param string $recurringType [Custom, Daily, Weekly, Monthly] Selecting any option except "Custom" will disregard the intervalDays parameter.
     * @param float $recurringIntervalDays // [value between 1 and 180 days]
     * @param float $recurringIterations // controls the frequency of charges to the customer for your services, Setting this parameter to "0" allows indefinite billing cycles until the recurring payment is canceled
     * @param float $recurringRetryCount // [between 0 to 5] determine how many attempts should be made to process a failed recurring payment before terminating the subscription
     *
     * @see https://paylinksa.readme.io/docs/recurring-payment
     */
    public function recurringPayment(
        float $paymentValue,
        string $customerName,
        string $customerMobile,
        string $recurringType,
        float $recurringIntervalDays,
        float $recurringIterations,
        float $recurringRetryCount,
        string $callbackUrl,
        ?string $currencyCode = null,
        ?string $customerEmail = null,
        ?string $paymentNote = null,
    ) {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // endpoint
        $endpoint = $this->apiLink . '/api/registerPayment';

        // Request body parameters
        $requestBody = [
            "payment" => [
                "value" => $paymentValue,
                "currencyCode" => $currencyCode,
                "paymentNote" => $paymentNote,
            ],
            "customer" => [
                "name" => $customerName,
                "mobile" => $customerMobile,
                "email" => $customerEmail
            ],
            "urls" => [
                "callback" => $callbackUrl
            ],
            "recurring" => [
                "type" => $recurringType,
                "intervalDays" => $recurringIntervalDays,
                "iterations" => $recurringIterations,
                "retryCount" => $recurringRetryCount
            ]
        ];

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
    }

    /**
     * Sending digital product information:
     * First, they must send the digital product information
     * to the customer through Paylink after the customer pays the order.
     * 
     * Then, paylink will forward the digital product information to the buyer's confirmed email.
     * 
     * @param string $message The digital product data such as file location in dropbox, card charge number, username and password for an account.
     * @param string $orderNumber Order number of the paid order.
     * 
     * @see https://paylinksa.readme.io/reference/sendproductinfotopayerusingpost
     */
    public function sendDigitalProduct(string $message, string $orderNumber)
    {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // endpoint
        $endpoint = $this->apiLink . '/api/sendDigitalProduct';

        // Request body parameters
        $requestBody = [
            'message' => $message,
            'orderNumber' => $orderNumber,
        ];

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
    }

    /** --------------------------------------------- HELPERS --------------------------------------------- */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getPaymentPageUrl(string $transactionNo): string
    {
        return $this->paymentPagePrefix . '/' . $transactionNo;
    }
}
