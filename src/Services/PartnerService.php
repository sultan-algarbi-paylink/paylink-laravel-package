<?php

namespace Paylink\Services;

use Illuminate\Support\Facades\Http;

class PartnerService
{
    /**
     * Paylink Service configration
     * @see https://paylinksa.readme.io/docs/partner-authentication#authentication
     */
    private string $apiLink;
    private string $profileNo;
    private string $apiKey;
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

            // config
            $this->profileNo = config('paylink.partner.production.profileNo');
            $this->apiKey = config('paylink.partner.production.apiKey');
            $this->persistToken = config('paylink.partner.production.persist_token');
        } else {
            // links
            $this->apiLink = 'https://restpilot.paylink.sa';

            // config
            $this->profileNo = config('paylink.partner.testing.profileNo');
            $this->apiKey = config('paylink.partner.testing.apiKey');
            $this->persistToken = config('paylink.partner.testing.persist_token');
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
            'profileNo' => $this->profileNo,
            'apiKey' => $this->apiKey,
            'persistToken' => $this->persistToken
        ];

        // endpoint
        $endpoint = $this->apiLink . '/api/partner/auth';

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
     * Get My Merchants:
     * allows partners to retrieve a list of merchants associated with their account.
     * 
     * @see https://paylinksa.readme.io/docs/paylink-partner-api-get-my-merchants
     */
    public function getMyMerchants()
    {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // endpoint
        $endpoint = $this->apiLink . '/rest/partner/getMyMerchants';

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->get($endpoint);

        return $response;
    }

    /**
     * Get Merchant Keys:
     * allows partners to retrieve API credentials (App ID and Secret Key)
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
     * @see https://paylinksa.readme.io/docs/paylink-partner-api-get-merchant-keys
     */
    public function getMerchantKeys(
        string $searchType,
        string $searchValue,
        string $profileNo,
    ) {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // endpoint
        $endpoint = $this->apiLink . "/rest/partner/getMerchantKeys/$searchType/$searchValue?profileNo=$profileNo";

        // Send a POST request to the server
        $response = Http::withHeaders([
            'accept' => '*/*',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->get($endpoint);

        return $response;
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
     * @see https://paylinksa.readme.io/docs/paylink-merchant-archive-api
     */
    public function archiveMerchant(
        string $key,
        string $keyType,
        string $partnerProfileNo,
    ) {
        if (app()->environment('local') || app()->environment('testing')) {
            if (empty($this->idToken)) {
                $this->_setIdToken();
            }

            // Request body parameters
            $requestBody = [
                'key' => $key,
                'keyType' => $keyType,
            ];

            // endpoint
            $endpoint = $this->apiLink . "/rest/partner/test/archive-merchant/$partnerProfileNo";

            // Send a POST request to the server
            $response = Http::withHeaders([
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->idToken,
            ])->post($endpoint, $requestBody);

            return $response;
        } else {
            return response()->json([
                'message' => 'This endpoint only work in testing environment.'
            ], 403);
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
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // Request body parameters
        $requestBody = [
            'registrationType' => $registrationType,
            'licenseNumber' => $licenseNumber,
            'mobileNumber' => $mobileNumber,
            'hijriYear' => $hijriYear,
            'hijriMonth' => $hijriMonth,
            'hijriDay' => $hijriDay,
            'partnerProfileNo' => $partnerProfileNo,
        ];

        // endpoint
        $endpoint = $this->apiLink . "/api/partner/register/check-license";

        // Send a POST request to the server
        $response = Http::withHeaders([
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
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
     * @see https://paylinksa.readme.io/docs/partner-registration-api#second-step-validate-mobile
     */
    public function validateMobile(
        string $signature,
        string $sessionUuid,
        string $mobile,
        string $otp,
        string $partnerProfileNo,
    ) {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // Request body parameters
        $requestBody = [
            'signature' => $signature,
            'sessionUuid' => $sessionUuid,
            'mobile' => $mobile,
            'otp' => $otp,
            'partnerProfileNo' => $partnerProfileNo,
        ];

        // endpoint
        $endpoint = $this->apiLink . "/api/partner/register/validate-otp";

        // Send a POST request to the server
        $response = Http::withHeaders([
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
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
        string $Email,
        string $firstName,
        string $lastName,
        string $password,
    ) {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // Request body parameters
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
            'Email' => $Email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'password' => $password,
        ];

        // endpoint
        $endpoint = $this->apiLink . "/api/partner/register/add-info";

        // Send a POST request to the server
        $response = Http::withHeaders([
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
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
     * @see https://paylinksa.readme.io/docs/partner-registration-api#fourth-step-confirming-account-with-nafath
     */
    public function confirmingWithNafath(
        string $signature,
        string $sessionUuid,
        string $mobile,
        string $partnerProfileNo,
    ) {
        if (empty($this->idToken)) {
            $this->_setIdToken();
        }

        // Request body parameters
        $requestBody = [
            'signature' => $signature,
            'sessionUuid' => $sessionUuid,
            'mobile' => $mobile,
            'partnerProfileNo' => $partnerProfileNo,
        ];

        // endpoint
        $endpoint = $this->apiLink . "/api/partner/register/confirm-account";

        // Send a POST request to the server
        $response = Http::withHeaders([
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->idToken,
        ])->post($endpoint, $requestBody);

        return $response;
    }
}
