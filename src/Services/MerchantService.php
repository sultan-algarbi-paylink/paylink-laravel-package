<?php

namespace Paylink\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Paylink\Models\PaylinkProduct;

class MerchantService
{
    /**
     * Valid card brands accepted by Paylink.
     *
     * @see https://paylinksa.readme.io/docs/payment-methods Official Paylink documentation for payment methods.
     */
    private const VALID_CARD_BRANDS = ['mada', 'visaMastercard', 'amex', 'tabby', 'tamara', 'stcpay', 'urpay'];

    /**
     * Paylink Service configration
     *
     * @see https://paylinksa.readme.io/docs/authentication#request-body-parameters Official Paylink API documentation for authentication.
     */
    private string $apiLink;
    private string $paymentPagePrefix;
    private string $apiId;
    private string $secretKey;
    private bool $persistToken;
    private string $idToken;

    /**
     * Initializes the MerchantService with configuration based on the current environment.
     */
    public function __construct()
    {
        if (app()->environment('production')) {
            // Production environment settings
            $this->apiLink = 'https://restapi.paylink.sa';
            $this->paymentPagePrefix = 'https://payment.paylink.sa/pay/order';

            // config
            $this->apiId = config('paylink.merchant.production.api_id');
            $this->secretKey = config('paylink.merchant.production.secret_key');
            $this->persistToken = config('paylink.merchant.production.persist_token', false);
        } else {
            // Testing environment settings
            $this->apiLink = 'https://restpilot.paylink.sa';
            $this->paymentPagePrefix = 'https://paymentpilot.paylink.sa/pay/info';

            // config
            $this->apiId = config('paylink.merchant.testing.api_id', 'APP_ID_1123453311');
            $this->secretKey = config('paylink.merchant.testing.secret_key', '0662abb5-13c7-38ab-cd12-236e58f43766');
            $this->persistToken = config('paylink.merchant.testing.persist_token', false);
        }
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
            // Prepare the request body with necessary parameters
            $requestBody = [
                'apiId' => $this->apiId,
                'secretKey' => $this->secretKey,
                'persistToken' => $this->persistToken
            ];

            // Construct the authentication endpoint URL
            $endpoint = $this->apiLink . '/api/auth';

            // Send a POST request to the authentication endpoint
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post($endpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check if the request failed or succeeded
            if ($response->failed() || empty($responseData) || empty($responseData['id_token'])) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Status code: " . $response->status();
                throw new Exception("Failed to authenticate. $errorMsg");
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
        ?string $currency = null,
        ?string $note = null,
        ?string $smsMessage = null,
        ?array $supportedCardBrands = [],
        ?bool $displayPending = true
    ) {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
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

            // Construct the endpoint URL
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
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->post($endpoint, $requestBody);

            // Decode the JSON response
            $orderDetails = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($orderDetails)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Status code: " . $response->status();
                throw new Exception("Failed to add the invoice. $errorMsg");
            }

            return $orderDetails;
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
    public function getInvoice(string $transactionNo)
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Prepare the API endpoint
            $endpoint = $this->apiLink . '/api/getInvoice/' . $transactionNo;

            // Send a GET request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->get($endpoint);

            // Decode the JSON response
            $orderDetails = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($orderDetails)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Status code: " . $response->status();
                throw new Exception("Failed to get the invoice. $errorMsg");
            }

            return $orderDetails;
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
     * @return void
     * 
     * @throws Exception If authentication fails or if there's an issue with canceling the invoice.
     * 
     * @see https://paylinksa.readme.io/docs/cancel-invoice
     */
    public function cancelInvoice(string $transactionNo)
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Prepare the API endpoint
            $endpoint = $this->apiLink . '/api/cancelInvoice';

            // Construct the request body
            $requestBody = [
                'transactionNo' => $transactionNo,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->post($endpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($responseData) || empty($responseData['success']) || $responseData['success'] != 'true') {
                $errorMsg = !empty($response->body()) ? $response->body() : "Status code: " . $response->status();
                throw new Exception("Failed to cancel the invoice. $errorMsg");
            }
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
    ) {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->_authentication();
            }

            // Filter and sanitize supportedCardBrands
            $filteredCardBrands = array_filter($supportedCardBrands, function ($brand) {
                return is_string($brand) && in_array($brand, self::VALID_CARD_BRANDS);
            });

            // Convert PaylinkProduct objects to arrays
            $productsArray = [];
            if (!empty($products)) {
                foreach ($products as $product) {
                    if ($product instanceof PaylinkProduct) {
                        $productsArray[] = $product->toArray();
                    }
                }
            }

            // Prepare the API endpoint
            $endpoint = $this->apiLink . '/api/payInvoice';

            // Construct the request body
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
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->post($endpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($responseData)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Status code: " . $response->status();
                throw new Exception("Failed to process the payment for this direct invoice. $errorMsg");
            }

            return $responseData;
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

            // Prepare the API endpoint
            $endpoint = $this->apiLink . '/api/registerPayment';

            // Construct the request body
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
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->post($endpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($responseData)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Status code: " . $response->status();
                throw new Exception("Failed to add this recurring payment. $errorMsg");
            }

            return $responseData;
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

            // Prepare the API endpoint
            $endpoint = $this->apiLink . '/api/sendDigitalProduct';

            // Construct the request body
            $requestBody = [
                'message' => $message,
                'orderNumber' => $orderNumber,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->post($endpoint, $requestBody);

            // Decode the JSON response
            $responseData = $response->json();

            // Check for request failure or empty response
            if ($response->failed() || empty($responseData)) {
                $errorMsg = !empty($response->body()) ? $response->body() : "Status code: " . $response->status();
                throw new Exception("Failed to process the payment for this direct invoice. $errorMsg");
            }

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
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
