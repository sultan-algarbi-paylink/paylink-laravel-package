<?php

namespace Paylink\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Paylink\Models\PaylinkProduct;
use Paylink\Models\PaylinkInvoiceResponse;

class PaylinkService
{
    // API URLs for production and test environments
    private const PRODUCTION_API_URL = 'https://restapi.paylink.sa';
    private const TEST_API_URL = 'https://restpilot.paylink.sa';

    // Payment Page URLs for production and test environments
    private const PRODUCTION_PAYMENT_PAGE_URL = 'https://payment.paylink.sa/pay/order';
    private const TEST_PAYMENT_PAGE_URL = 'https://paymentpilot.paylink.sa/pay/info';

    // Default credentials for the test environment
    private const DEFAULT_TEST_API_ID = 'APP_ID_1123453311';
    private const DEFAULT_TEST_SECRET_KEY = '0662abb5-13c7-38ab-cd12-236e58f43766';

    // Valid card brands accepted by Paylink.
    private const VALID_CARD_BRANDS = ['mada', 'visaMastercard', 'amex', 'tabby', 'tamara', 'stcpay', 'urpay'];

    // Properties
    private string $apiBaseUrl;
    private string $paymentBaseUrl;
    private string $apiId;
    private string $secretKey;
    private bool $persistToken = false;
    private ?string $idToken;

    /**
     * PaylinkService constructor.
     *
     * @param string $environment
     * @param string|null $apiId
     * @param string|null $secretKey
     */
    public function __construct(?string $environment, ?string $apiId = null, ?string $secretKey = null)
    {
        // Set environment
        $environment ??= app()->environment();

        // Determine the base URL based on the environment
        $this->apiBaseUrl = $environment === 'production' ? self::PRODUCTION_API_URL : self::TEST_API_URL;
        $this->paymentBaseUrl = $environment === 'production' ? self::PRODUCTION_PAYMENT_PAGE_URL : self::TEST_PAYMENT_PAGE_URL;

        // Determine API ID and Secret Key
        $this->apiId = $environment === 'production' ? $apiId : self::DEFAULT_TEST_API_ID;
        $this->secretKey = $environment === 'production' ? $secretKey : self::DEFAULT_TEST_SECRET_KEY;
        $this->idToken = null;

        if (is_null($this->apiId) || is_null($this->secretKey)) {
            throw new \InvalidArgumentException('API_ID and Secret_Key are required for the production environment');
        }
    }

    /**
     * Initialize the Paylink client for the test environment.
     *
     * @return static
     */
    public static function test(): self
    {
        return new self('test');
    }

    /**
     * Initialize the Paylink client for the production environment.
     *
     * @param string $apiId
     * @param string $secretKey
     * @return static
     */
    public static function production(string $apiId, string $secretKey): self
    {
        return new self('production', $apiId, $secretKey);
    }

    /**
     * Authenticates with the Paylink API and retrieves an authentication token.
     *
     * This method is the initial step in using the Paylink API. The authentication token obtained here
     * is crucial for subsequent endpoint calls, as it authenticates and authorizes the merchant's system.
     * 
     * @throws Exception If authentication fails or if the token is not found in the response.
     * 
     * @see https://paylinksa.readme.io/docs/authentication Official Paylink API authentication documentation.
     */
    private function _authentication()
    {
        try {
            // Request Endpoint
            $requestEndpoint = "$this->apiBaseUrl/api/auth";

            // Request headers
            $requestHeader = [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ];

            // Request body parameters
            $requestBody = [
                'apiId' => $this->apiId,
                'secretKey' => $this->secretKey,
                'persistToken' => $this->persistToken
            ];

            // Send a POST request to the authentication endpoint
            $response = Http::withHeaders($requestHeader)->post($requestEndpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check if the request failed or succeeded
            if ($response->failed() || empty($responseData) || empty($responseData['id_token'])) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Failed to authenticate. Status code: $response->status()";
                throw new Exception($errorMsg, $response->status());
            }

            // Set the authentication token for future API calls
            $this->idToken = $responseData['id_token'];
        } catch (Exception $e) {
            // Reset the authentication token on error
            $this->idToken = null;
            throw $e;
        }
    }

    /** --------------------------------------------- Invoice Operations --------------------------------------------- */

    /**
     * Adds an invoice to the Paylink system.
     *
     * This method enables merchants to generate invoices and receive payments online through the Paylink gateway.
     * 
     * @param float $amount The total amount of the invoice. NOTE: Buyer will pay this amount regardless of the total amounts of the products' prices.
     * @param string $clientMobile The mobile number of the client.
     * @param string $clientName The name of the client.
     * @param string $orderNumber A unique identifier for the invoice.
     * @param PaylinkProduct[] $products An array of PaylinkProduct objects to be included in the invoice.
     * @param string $callBackUrl Call back URL that will be called by Paylink to the merchant system. This URL will receive two parameters: orderNumber and transactionNo.
     * @param string|null $cancelUrl Call back URL to cancel orders that will be called by Paylink to the merchant system. This URL will receive two parameters: orderNumber and transactionNo.
     * @param string|null $clientEmail The email address of the client.
     * @param string|null $currency The currency code of the invoice. The default value is SAR. (e.g., USD, EUR, GBP).
     * @param string|null $note A note for the invoice.
     * @param string|null $smsMessage This option will enable the invoice to be sent to the client's mobile specified in clientMobile.
     * @param array|null $supportedCardBrands List of supported card brands. This list is optional. Supported values are: [mada, visaMastercard, amex, tabby, tamara, stcpay, urpay].
     * @param bool|null $displayPending This option will make this invoice displayed in my.paylink.sa.
     * 
     * @return array Returns the details of the added invoice.
     * 
     * @throws Exception If adding the invoice fails.
     * 
     * @see https://paylinksa.readme.io/docs/invoices Official Paylink API documentation for invoices.
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
        ?string $currency = 'SAR',
        ?string $note = null,
        ?string $smsMessage = null,
        ?array $supportedCardBrands = [],
        ?bool $displayPending = true
    ): PaylinkInvoiceResponse {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Filter and sanitize supportedCardBrands
            $filteredCardBrands = array_filter($supportedCardBrands, function ($brand): bool {
                return is_string($brand) && in_array($brand, self::VALID_CARD_BRANDS);
            });

            // Convert PaylinkProduct objects to arrays
            $productsArray = [];
            foreach ($products as $index => $product) {
                if ($product instanceof PaylinkProduct) {
                    $productsArray[] = $product->toArray();
                } else {
                    throw new \InvalidArgumentException("Invalid product type at index $index, Each product must be an instance of Paylink\Models\PaylinkProduct.");
                }
            }

            // Request Endpoint
            $requestEndpoint = "$this->apiBaseUrl/api/addInvoice";

            // Request headers
            $requestHeader = [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer $this->idToken",
            ];

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
            $response = Http::withHeaders($requestHeader)->post($requestEndpoint, $requestBody);

            // Decode the JSON response
            $orderDetails = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($orderDetails)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Failed to add the invoice. Status code: $response->status()";
                throw new Exception($errorMsg, $response->status());
            }

            return PaylinkInvoiceResponse::fromResponseData($orderDetails);
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Retrieves invoice details
     *
     * This endpoint is used by the merchant's application to get an invoice details using the "TransactionNo."
     * 
     * @param string $transactionNo The transaction number of the invoice to retrieve.
     * 
     * @return array Returns the invoice details.
     * 
     * @throws Exception If authentication fails or if there's an issue with retrieving the invoice.
     * 
     * @see https://paylinksa.readme.io/docs/order-request
     */
    public function getInvoice(string $transactionNo): PaylinkInvoiceResponse
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Request Endpoint
            $requestEndpoint = "$this->apiBaseUrl/api/getInvoice/$transactionNo";

            // Request headers
            $requestHeader = [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer $this->idToken",
            ];

            // Send a GET request to the server
            $response = Http::withHeaders($requestHeader)->get($requestEndpoint);

            // Decode the JSON response
            $orderDetails = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($orderDetails)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Failed to get the invoice. Status code: $response->status()";
                throw new Exception($errorMsg, $response->status());
            }

            return PaylinkInvoiceResponse::fromResponseData($orderDetails);
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Cancels an existing invoice.
     *
     * This method enables the cancellation of an existing transaction initiated by a merchant using the Paylink payment gateway.
     * 
     * @param string $transactionNo The transaction number to be canceled.
     * 
     * @return bool
     * 
     * @throws Exception If authentication fails or if there's an issue with canceling the invoice.
     * 
     * @see https://paylinksa.readme.io/docs/cancel-invoice
     */
    public function cancelInvoice(string $transactionNo): bool
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Request Endpoint
            $requestEndpoint = "$this->apiBaseUrl/api/cancelInvoice";

            // Request headers
            $requestHeader = [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer $this->idToken",
            ];

            // Request body parameters
            $requestBody = [
                'transactionNo' => $transactionNo,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders($requestHeader)->post($requestEndpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($responseData) || empty($responseData['success'])) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Failed to cancel the invoice. Status code: $response->status()";
                throw new Exception($errorMsg, $response->status());
            }

            return $responseData['success'] === 'true';
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /** --------------------------------------------- Extra Functions --------------------------------------------- */

    /**
     * Process payment for invoices using direct integration with Paylink, including card information.
     *
     * This endpoint is designed for merchants to create invoices and accept online payments through the Paylink gateway.
     * The integration process collects card information directly from customers for payment processing on your webpage.
     * Immediate payment status results are provided without webpage redirections.
     * 
     * @param float $amount The total amount of the invoice. NOTE: Buyer will pay this amount regardless of the total amounts of the products' prices.
     * @param string $clientMobile The mobile number of the client.
     * @param string $clientName The name of the client.
     * @param string $orderNumber A unique identifier for the invoice.
     * @param PaylinkProduct[] $products An array of PaylinkProduct objects to be included in the invoice.
     * @param string $cardNumber The card number for payment.
     * @param string $cardSecurityCode The security code (CVV) for the card.
     * @param string $cardExpiryMonth The expiry month of the card (MM format).
     * @param string $cardExpiryYear The expiry year of the card (YY format).
     * @param string $callBackUrl The URL called when payment is complete or canceled.
     * @param string|null $cancelUrl The URL for canceling the payment.
     * @param string|null $clientEmail The email address of the client.
     * @param string|null $currency The currency code of the invoice.
     * @param string|null $note A note for the invoice.
     * @param string|null $smsMessage An SMS message for the client.
     * @param array|null $supportedCardBrands List of supported card brands, values are: [mada, visaMastercard, amex, tabby, tamara, stcpay, urpay]
     * @param bool|null $displayPending Option to display the invoice in my.paylink.sa.
     * 
     * @return array|null Returns the response data from the server or null if the request fails.
     * 
     * @throws Exception If authentication fails or if there's an issue with processing the payment.
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
    ): PaylinkInvoiceResponse {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Filter and sanitize supportedCardBrands
            $filteredCardBrands = array_filter($supportedCardBrands, function ($brand): bool {
                return is_string($brand) && in_array($brand, self::VALID_CARD_BRANDS);
            });

            // Convert PaylinkProduct objects to arrays
            $productsArray = [];
            foreach ($products as $index => $product) {
                if ($product instanceof PaylinkProduct) {
                    $productsArray[] = $product->toArray();
                } else {
                    throw new \InvalidArgumentException("Invalid product type at index $index, Each product must be an instance of Paylink\Models\PaylinkProduct.");
                }
            }

            // Request Endpoint
            $requestEndpoint = "$this->apiBaseUrl/api/payInvoice";

            // Request headers
            $requestHeader = [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer $this->idToken",
            ];

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
            $response = Http::withHeaders($requestHeader)->post($requestEndpoint, $requestBody);

            // Decode the JSON response
            $orderDetails = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($orderDetails)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Failed to process the payment for this direct invoice. Status code: $response->status()";
                throw new Exception($errorMsg, $response->status());
            }

            return PaylinkInvoiceResponse::fromResponseData($orderDetails);
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Recurring Payment to the system:
     *
     * Enables you to initiate recurring payments and view all active regular payments in the system.
     *
     * @param float $paymentValue The amount of the payment.
     * @param string|null $currencyCode The currency code (e.g., USD, EUR).
     * @param string|null $paymentNote Additional notes or information about the payment.
     * @param string $customerName The name of the customer.
     * @param string $customerMobile The mobile number of the customer.
     * @param string|null $customerEmail The email address of the customer.
     * @param string $callbackUrl The URL to which payment notifications/callbacks will be sent.
     * @param string $recurringType The type of recurring payment (Custom, Daily, Weekly, Monthly).
     * @param float $recurringIntervalDays The interval in days for recurring payments (valid for Custom type).
     * @param float $recurringIterations The number of iterations for the recurring payment (0 for indefinite billing cycles).
     * @param float $recurringRetryCount The number of retry attempts for failed recurring payments (0 to 5).
     * 
     * @return array|null Returns the response data from the server or null if the request fails.
     * 
     * @throws Exception If authentication fails or if there's an issue with adding the recurring payment.
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
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Request Endpoint
            $requestEndpoint = "$this->apiBaseUrl/api/registerPayment";

            // Request headers
            $requestHeader = [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer $this->idToken",
            ];

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
            $response = Http::withHeaders($requestHeader)->post($requestEndpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($responseData)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Failed to add this recurring payment. Status code: $response->status()";
                throw new Exception($errorMsg, $response->status());
            }

            $result = [];

            if (!empty($responseData['response'])) {
                $result['response'] = [
                    "isSuccess" => $responseData['response']['isSuccess'],
                    "message" => $responseData['response']['message'],
                    "validationErrors" => $responseData['response']['validationErrors'],
                ];
            }

            if (!empty($responseData['invoiceDetails'])) {
                $result['invoiceDetails'] = [
                    "paymentUrl" => $responseData['invoiceDetails']['paymentUrl'],
                    "customerReference" => $responseData['invoiceDetails']['customerReference'],
                    "userDefinedField" => $responseData['invoiceDetails']['userDefinedField'],
                    "recurringId" => $responseData['invoiceDetails']['recurringId'],
                ];
            }

            return $result;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Sending digital product information:
     * 
     * First, they must send the digital product information
     * to the customer through Paylink after the customer pays the order.
     * 
     * Then, paylink will forward the digital product information to the buyer's confirmed email.
     * 
     * @param string $message The digital product data such as file location in dropbox, card charge number, username and password for an account.
     * @param string $orderNumber Order number of the paid order.
     * 
     * @return array|null Returns the response data from the server or null if the request fails.
     * 
     * @throws Exception If authentication fails or if there's an issue with processing the payment.
     * 
     * @see https://paylinksa.readme.io/reference/sendproductinfotopayerusingpost
     */
    public function sendDigitalProduct(string $message, string $orderNumber)
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Request Endpoint
            $requestEndpoint = "$this->apiBaseUrl/api/sendDigitalProduct";

            // Request headers
            $requestHeader = [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer $this->idToken",
            ];

            // Request body parameters
            $requestBody = [
                'message' => $message,
                'orderNumber' => $orderNumber,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders($requestHeader)->post($requestEndpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($responseData)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Failed to process the payment for this direct invoice. Status code: $response->status()";
                throw new Exception($errorMsg, $response->status());
            }

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /** --------------------------------------------- HELPERS --------------------------------------------- */
    public function getPaymentPageUrl(string $transactionNo): string
    {
        return "$this->paymentBaseUrl/$transactionNo";
    }
}
