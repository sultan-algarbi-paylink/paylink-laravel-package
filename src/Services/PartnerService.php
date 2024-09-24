<?php

namespace Paylink\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class PartnerService
{
    // API URLs for production and test environments
    private const PRODUCTION_API_URL = 'https://restapi.paylink.sa';
    private const TEST_API_URL = 'https://restpilot.paylink.sa';

    /**
     * Paylink Service configration
     * @see https://paylinksa.readme.io/docs/partner-authentication#authentication
     */

    private string $environment;
    private string $apiBaseUrl;
    private string $profileNo;
    private string $apiKey;
    private bool $persistToken;
    private string $idToken;

    /**
     * PartnerService constructor.
     *
     * @param string $environment
     * @param string $profileNo
     * @param string $apiKey
     */
    public function __construct(string $environment, string $profileNo, string $apiKey)
    {
        $this->apiBaseUrl = $environment === 'production' ? self::PRODUCTION_API_URL : self::TEST_API_URL;
        $this->environment = $environment;
        $this->profileNo = $profileNo;
        $this->apiKey = $apiKey;
        $this->persistToken = false;
        $this->idToken = null;

        if (is_null($this->profileNo) || is_null($this->apiKey)) {
            throw new \InvalidArgumentException('Profile_No and API_Key are required.');
        }
    }

    /**
     * Initialize the Paylink Partner for the test environment.
     *
     * @param string $profileNo
     * @param string $apiKey
     * @return static
     */
    public static function test(string $profileNo, string $apiKey): self
    {
        return new self('test', $profileNo,  $apiKey);
    }

    /**
     * Initialize the Paylink Partner for the production environment.
     *
     * @param string $profileNo
     * @param string $apiKey
     * @return static
     */
    public static function production(string $profileNo, string $apiKey): self
    {
        return new self('production', $profileNo, $apiKey);
    }

    /** 
     * 
     * Authenticate with the Paylink API and obtain an authentication token.
     * The authentication token is essential for subsequent API calls.
     * 
     * @throws Exception if authentication fails.
     * 
     * @see https://paylinksa.readme.io/docs/authentication
     */
    private function authentication()
    {
        try {
            // Construct the authentication endpoint URL
            $endpoint = "{$this->apiBaseUrl}/api/partner/auth";

            // Prepare the request body with necessary parameters
            $requestBody = [
                'profileNo' => $this->profileNo,
                'apiKey' => $this->apiKey,
                'persistToken' => $this->persistToken,
            ];

            // Send a POST request to the authentication endpoint
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post($endpoint, $requestBody);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to authenticate');
            }

            // Decode the JSON response
            $responseData = $response->json();

            if (empty($responseData['id_token'])) {
                throw new Exception('Authentication token missing in the response.');
            }

            // Store the token for future API calls
            $this->idToken = $responseData['id_token'];
        } catch (Exception $e) {
            // In case of any exception, clear the token and rethrow the error
            $this->idToken = null;
            throw $e;
        }
    }

    /** -------------------------------- Partner Registration -------------------------------- */

    /**
     * First step: Check License
     * The first step for registration is getting license information from the merchant.
     * The license could be a Saudi freelance certificate or a Saudi Commercial Registration.
     * 
     * @param string $registrationType Enum [freelancer, cr]
     * @param string $licenseNumber It will contain either the number of the freelance certificate or the number of the commercial registration.
     * @param string $mobileNumber The mobile number of the merchant
     * @param string $hijriYear The Hijra year of the merchant's birth date.
     * @param string $hijriMonth The Hijra month of the birth date of the merchant
     * @param string $hijriDay The Hijra day of the birth date of the merchant
     * @param string $partnerProfileNo The partner profile no. It will be given upon signing the contract with Paylink.
     * 
     * @return mixed The response data from the API.
     * 
     * @throws Exception if the license check fails.
     * 
     * @see https://paylinksa.readme.io/docs/partner-registration-api#first-step-check-license
     */
    public function checkLicense(
        string $registrationType,
        string $licenseNumber,
        string $mobileNumber,
        string $hijriYear,
        string $hijriMonth,
        string $hijriDay,
        string $partnerProfileNo,
    ) {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Prepare the API endpoint
            $endpoint = "{$this->apiBaseUrl}/api/partner/register/check-license";

            // Construct the request body
            $requestBody = [
                'registrationType' => $registrationType,
                'licenseNumber' => $licenseNumber,
                'mobileNumber' => $mobileNumber,
                'hijriYear' => $hijriYear,
                'hijriMonth' => $hijriMonth,
                'hijriDay' => $hijriDay,
                'partnerProfileNo' => $partnerProfileNo,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ])->post($endpoint, $requestBody);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to check license');
            }

            // Decode the JSON response
            $responseData = $response->json();

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Second step: Validate Mobile
     * The second step for registration is validating the merchant's mobile number.
     * In this step, the merchant will receive an SMS containing OTP to send to the server.
     * 
     * @param string $signature The signature is received from the first step. It must be passed as is.
     * @param string $sessionUuid The registration session UUID.
     * @param string $mobile It is the mobile number of the merchant.
     * @param string $otp The OTP message in the SMS is received on the merchant's mobile device.
     * @param string $partnerProfileNo The partner profile no. It will be given upon signing the contract with Paylink.
     * 
     * @return mixed The response data from the API.
     * 
     * @throws Exception if mobile validation fails.
     * 
     * @see https://paylinksa.readme.io/docs/partner-registration-api#second-step-validate-mobile
     */
    public function validateMobile(
        string $signature,
        string $sessionUuid,
        string $mobile,
        string $otp,
        string $partnerProfileNo,
    ) {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Prepare the API endpoint
            $endpoint = "{$this->apiBaseUrl}/api/partner/register/validate-otp";

            // Construct the request body
            $requestBody = [
                'signature' => $signature,
                'sessionUuid' => $sessionUuid,
                'mobile' => $mobile,
                'otp' => $otp,
                'partnerProfileNo' => $partnerProfileNo,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders([
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ])->post($endpoint, $requestBody);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to validate mobile');
            }

            // Decode the JSON response
            $responseData = $response->json();

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Third step: Add Information for bank and merchant.
     * The third step for registration is adding Information related to the merchant,
     * such as a bank, IBAN, email, and password.
     * 
     * @param string $signature The signature is received from the first step. It must be passed as is.
     * @param string $sessionUuid The registration session UUID.
     * @param string $mobile It is the mobile number of the merchant.
     * @param string $partnerProfileNo The partner profile no. It will be given upon signing the contract with Paylink.
     * @param string $iban It is the merchant's bank IBAN.
     * @param string $bankName It is the bank name of the merchant.
     * @param string $categoryDescription Description of the merchant business. Merchant must describe their business and activity in a free text value.
     * @param string $salesVolume It is the volume of merchant sales per month.
     * @param string $sellingScope [global, domestic] Choose only two values: domestic to sell in Saudi Arabia or global to sell internationally.
     * @param string $nationalId It is the national ID of the merchant.
     * @param string $licenseName It is the actual name of the merchant in the license here.
     * @param string $Email Email of the merchant
     * @param string $firstName The first name of the merchant
     * @param string $lastName The last name of the merchant.
     * @param string $password The password of the merchant.
     * 
     * @return mixed The response data from the API.
     * 
     * @throws Exception if adding information fails.
     * 
     * @see https://paylinksa.readme.io/docs/partner-registration-api#third-step-add-information-for-bank-and-merchant
     */
    public function addInfo(
        string $signature,
        string $sessionUuid,
        string $mobile,
        string $partnerProfileNo,
        string $iban,
        string $bankName,
        string $categoryDescription,
        string $salesVolume,
        string $sellingScope,
        string $nationalId,
        string $licenseName,
        string $email,
        string $firstName,
        string $lastName,
        string $password,
    ) {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Prepare the API endpoint
            $endpoint = "{$this->apiBaseUrl}/api/partner/register/add-info";

            // Construct the request body
            $requestBody = [
                'signature' => $signature,
                'sessionUuid' => $sessionUuid,
                'mobile' => $mobile,
                'partnerProfileNo' => $partnerProfileNo,
                'iban' => $iban,
                'bankName' => $bankName,
                'categoryDescription' => $categoryDescription,
                'salesVolume' => $salesVolume,
                'sellingScope' => $sellingScope,
                'nationalId' => $nationalId,
                'licenseName' => $licenseName,
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'password' => $password,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders([
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ])->post($endpoint, $requestBody);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to add information');
            }

            // Decode the JSON response
            $responseData = $response->json();

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Fourth step: Confirming Account with Nafath
     * The fourth step for registration is confirming
     * the account with Nafath after submitting the third step.
     * The merchant will open the Nafath App to approve the application and verify identity.
     * 
     * @param string $signature The signature is received from the first step. It must be passed as is.
     * @param string $sessionUuid The registration session UUID.
     * @param string $mobile It is the mobile number of the merchant.
     * @param string $partnerProfileNo The partner profile no. It will be given upon signing the contract with Paylink.
     * 
     * @return mixed The response data from the API.
     * 
     * @throws Exception if confirming with Nafath fails.
     * 
     * @see https://paylinksa.readme.io/docs/partner-registration-api#fourth-step-confirming-account-with-nafath
     */
    public function confirmingWithNafath(
        string $signature,
        string $sessionUuid,
        string $mobile,
        string $partnerProfileNo,
    ) {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Prepare the API endpoint
            $endpoint = "{$this->apiBaseUrl}/api/partner/register/confirm-account";

            // Construct the request body
            $requestBody = [
                'signature' => $signature,
                'sessionUuid' => $sessionUuid,
                'mobile' => $mobile,
                'partnerProfileNo' => $partnerProfileNo,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ])->post($endpoint, $requestBody);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to confirm with Nafath');
            }

            // Decode the JSON response
            $responseData = $response->json();

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /** -------------------------------- Extra Functions -------------------------------- */

    /**
     * Get My Merchants:
     * allows partners to retrieve a list of merchants associated with their account.
     * 
     * @return mixed The response data from the API.
     * 
     * @throws Exception if getting merchants fails.
     * 
     * @see https://paylinksa.readme.io/docs/paylink-partner-api-get-my-merchants
     */
    public function getMyMerchants()
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Prepare the API endpoint
            $endpoint = "{$this->apiBaseUrl}/rest/partner/getMyMerchants";

            // Send a GET request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ])->get($endpoint);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to get your merchants');
            }

            // Decode the JSON response
            $responseData = $response->json();

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Get Merchant Keys:
     * allows partners to retrieve API credentials (API ID and Secret Key)
     * for a specific sub-merchant.
     * 
     * @param string $searchType Path variable for the search type. [cr, freelancer, mobile, email, accountNo]
     * @param string $searchValue Path variable for the search value
     * @param string $profileNo Profile number of the partner associated with the merchant.
     * 
     * # searchType:
     *  - cr: Commercial Registration of the merchant (20139202930)
     *  - freelancer: Freelancer document of the merchant (FL-391666498)
     *  - mobile: Mobile of the merchant (0509200900)
     *  - email: E-Mail of the merchant (it@merchant.com)
     *  - accountNo: The account number of the merchant in Paylink (3199210810102450)
     * 
     * @return mixed The response data from the API.
     * 
     * @throws Exception if retrieving merchant keys fails.
     * 
     * @see https://paylinksa.readme.io/docs/paylink-partner-api-get-merchant-keys
     */
    public function getMerchantKeys(
        string $searchType,
        string $searchValue,
        string $profileNo,
    ) {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Prepare the API endpoint
            $endpoint = "{$this->apiBaseUrl}/rest/partner/getMerchantKeys/{$searchType}/{$searchValue}?profileNo={$profileNo}";

            // Send a GET request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ])->get($endpoint);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to retrieve API credentials of the merchant');
            }

            // Decode the JSON response
            $responseData = $response->json();

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Archive Merchant API:
     * This API endpoint lets you archive a merchant using a specific key, like a mobile number.
     * 
     * @param string $key This is the actual value like a mobile number.
     * @param string $keyType Type of the key being passed, e.g., mobile. [cr, freelancer, mobile, email, accountNo]
     * @param string $partnerProfileNo the specific partner profile ID you wish to archive.
     * 
     * # keyType:
     *  - cr: Commercial Registration of the merchant (20139202930)
     *  - freelancer: Freelancer document of the merchant (FL-391666498)
     *  - mobile: Mobile of the merchant (0509200900)
     *  - email: E-Mail of the merchant (it@merchant.com)
     *  - accountNo: The account number of the merchant in Paylink (3199210810102450)
     * 
     * @return mixed The response data from the API.
     * 
     * @throws Exception if archiving the merchant fails.
     * 
     * @see https://paylinksa.readme.io/docs/paylink-merchant-archive-api
     */
    public function archiveMerchant(
        string $keyType,
        string $keyValue,
        string $partnerProfileNo,
    ) {
        try {
            // Check the current environment
            if (!(app()->environment('local') || app()->environment('testing'))) {
                throw new Exception('This endpoint only work in testing environment.');
            }

            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Prepare the API endpoint
            $endpoint = "{$this->apiBaseUrl}/rest/partner/test/archive-merchant/{$partnerProfileNo}";

            // Construct the request body
            $requestBody = [
                'keyType' => $keyType,
                'key' => $keyValue,
            ];

            // Send a POST request to the server
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ])->post($endpoint, $requestBody);

            // Check if the request was successful
            if ($response->failed()) {
                $this->handleResponseError($response, 'Failed to archive this merchant');
            }

            // Decode the JSON response
            $responseData = $response->json();

            return $responseData;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /** --------------------------------------------- HELPERS --------------------------------------------- */
    /**
     * Handle errors in Paylink response.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @throws \Exception
     */
    private function handleResponseError($response, string $defaultErrorMsg)
    {
        // Try to extract error details from the response body
        $responseData = $response->json();
        $errorMsg = $responseData['detail'] ?? $responseData['title'] ?? $responseData['error'] ?? $response->body();

        if (empty($errorMsg)) {
            $errorMsg = $defaultErrorMsg;
        }

        // Include the status code in the error message for debugging purposes
        $errorMsg .= ", Status code: {$response->status()}";

        throw new Exception($errorMsg, $response->status());
    }
}
