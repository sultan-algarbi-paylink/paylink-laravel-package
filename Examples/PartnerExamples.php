<?php

namespace Paylink\Examples;

use App\Http\Controllers\Controller;
use Exception;

// Import Paylink Package
use Paylink\Services\PartnerService;

class PartnerController extends Controller
{
    /** -------------------------------- Partner Registration -------------------------------- */
    // First step: Checks license for partner registration
    public function checkLicense()
    {
        try {
            // Create an instance of PartnerService
            $partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');
            // $partnerService = PartnerService::production('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');

            // Call Paylink to check license
            $responseData = $partnerService->checkLicense(
                registrationType: "cr", // freelancer or cr
                licenseNumber: "7014832310",
                mobileNumber: "0512345678",
                hijriYear: "1400",
                hijriMonth: "06",
                hijriDay: "16",
                partnerProfileNo: "07537924"
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Second step: Validates the mobile number for partner registration
    public function validateMobile()
    {
        try {
            // Create an instance of PartnerService
            $partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');

            // Call Paylink to validate mobile number
            $responseData = $partnerService->validateMobile(
                signature: "ae135f2506dc3c44152d62265419c09e80dec0b108090bc81d6a1a691c3f0647",
                mobile: "0512345678",
                sessionUuid: "96ea8e22-edef-414b-9724-3bd2d494b710",
                otp: "7615",
                partnerProfileNo: "19039481"
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Third step: Adds additional information for partner registration
    public function addInfo()
    {
        try {
            // Create an instance of PartnerService
            $partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');

            // Call Paylink to add additional information
            $responseData = $partnerService->addInfo(
                mobile: "0500000001",
                sessionUuid: "96ea8e22-edef-414b-9724-3bd2d494b710",
                signature: "ae135f2506dc3c44152d62265419c09e80dec0b108090bc81d6a1a691c3f0647",
                partnerProfileNo: "19039481",
                iban: "SA1231231231312312313213",
                bankName: "AlRajhi Bank",
                categoryDescription: "Any description for the activity of the merchant. It must match the activity of the merchant.",
                salesVolume: "below_10000",
                sellingScope: "domestic",
                nationalId: "1006170383",
                licenseName: '21012451525',
                email: "mohammed@test.com",
                firstName: "Mohammed",
                lastName: "Ali",
                password: "xxxxxxxxxxx",
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Fourth step: Confirms partner registration with Nafath
    public function confirmingWithNafath()
    {
        try {
            // Create an instance of PartnerService
            $partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');

            // Call Paylink to confirm partner registration with Nafath
            $responseData = $partnerService->confirmingWithNafath(
                signature: 'ae135f2506dc3c44152d62265419c09e80dec0b108090bc81d6a1a691c3f0647',
                sessionUuid: '96ea8e22-edef-414b-9724-3bd2d494b710',
                mobile: '0512345678',
                partnerProfileNo: '19039481',
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    /** -------------------------------- Extra Functions -------------------------------- */
    // Retrieves merchants associated with the partner
    public function getMyMerchants()
    {
        try {
            // Create an instance of PartnerService
            $partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');

            // Call Paylink to retrieve merchants
            $responseData = $partnerService->getMyMerchants();

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Retrieves merchant keys based on search criteria
    public function getMerchantKeys()
    {
        try {
            // Create an instance of PartnerService
            $partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');

            // Call Paylink to retrieve merchant keys
            $responseData = $partnerService->getMerchantKeys(
                searchType: 'cr', // cr, freelancer, mobile, email, accountNo
                searchValue: '20139202930',
                profileNo: '12345687',
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Archives a merchant
    public function archiveMerchant()
    {
        try {
            // Create an instance of PartnerService
            $partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');

            // Call Paylink to archive a merchant
            $responseData = $partnerService->archiveMerchant(
                keyType: 'freelancer', // cr, freelancer, mobile, email, accountNo
                keyValue: '20139202930',
                partnerProfileNo: '1235478',
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }
}
