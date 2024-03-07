<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Import Paylink Package
use Paylink\Services\PartnerService;

class PartnerController extends Controller
{
    // Retrieves merchants associated with the partner
    public function getMyMerchants(Request $request)
    {
        // Create an instance of PartnerService
        $partnerService = new PartnerService();

        // Call Paylink to retrieve merchants
        $response = $partnerService->getMyMerchants();

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Retrieves merchant keys based on search criteria
    public function getMerchantKeys(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'searchType' => 'required|string|in:cr,freelancer,mobile,email,accountNo',
            'searchValue' => 'required|string',
            'profileNo' => 'required|string',
        ]);

        // Create an instance of PartnerService
        $partnerService = new PartnerService();

        // Call Paylink to retrieve merchant keys
        $response = $partnerService->getMerchantKeys(
            searchType: $request->input('searchType'), // cr, freelancer, mobile, email, accountNo
            searchValue: $request->input('searchValue'),
            profileNo: $request->input('profileNo'),
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Archives a merchant
    public function archiveMerchant(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'key' => 'required|string',
            'keyType' => 'required|string|in:cr,freelancer,mobile,email,accountNo',
            'partnerProfileNo' => 'required|string',
        ]);

        // Create an instance of PartnerService
        $partnerService = new PartnerService();

        // Call Paylink to archive a merchant
        $response = $partnerService->archiveMerchant(
            key: $request->input('key'),
            keyType: $request->input('keyType'), // cr, freelancer, mobile, email, accountNo
            partnerProfileNo: $request->input('partnerProfileNo'),
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    /** -------------------------------- Partner Registration -------------------------------- */
    // First step: Checks license for partner registration
    public function checkLicense(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'registrationType' => 'required|string|in:freelancer,cr',
            'licenseNumber' => 'required|string',
            'mobileNumber' => 'required|string',
            'hijriYear' => 'required|string',
            'hijriMonth' => 'required|string',
            'hijriDay' => 'required|string',
            'partnerProfileNo' => 'required|string',
        ]);

        // Create an instance of PartnerService
        $partnerService = new PartnerService();

        // Call Paylink to check license
        $response = $partnerService->checkLicense(
            registrationType: $request->input('registrationType'), // freelancer or cr
            licenseNumber: $request->input('licenseNumber'),
            mobileNumber: $request->input('mobileNumber'),
            hijriYear: $request->input('hijriYear'),
            hijriMonth: $request->input('hijriMonth'),
            hijriDay: $request->input('hijriDay'),
            partnerProfileNo: $request->input('partnerProfileNo'),
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Second step: Validates mobile number for partner registration
    public function validateMobile(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'signature' => 'required|string',
            'sessionUuid' => 'required|string',
            'mobile' => 'required|string',
            'otp' => 'required|string',
            'partnerProfileNo' => 'required|string',
        ]);

        // Create an instance of PartnerService
        $partnerService = new PartnerService();

        // Call Paylink to validate mobile number
        $response = $partnerService->validateMobile(
            signature: $request->input('signature'),
            sessionUuid: $request->input('sessionUuid'),
            mobile: $request->input('mobile'),
            otp: $request->input('otp'),
            partnerProfileNo: $request->input('partnerProfileNo'),
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Third step: Adds additional information for partner registration
    public function addInfo(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'signature' => 'required|string',
            'sessionUuid' => 'required|string',
            'mobile' => 'required|string',
            'partnerProfileNo' => 'required|string',
            'iban' => 'nullable|string',
            'bankName' => 'nullable|string',
            'categoryDescription' => 'nullable|string',
            'salesVolume' => 'nullable|numeric',
            'sellingScope' => 'nullable|string|in:global,domestic',
            'nationalId' => 'nullable|string',
            'licenseName' => 'nullable|string',
            'Email' => 'nullable|email',
            'firstName' => 'nullable|string',
            'lastName' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        // Create an instance of PartnerService
        $partnerService = new PartnerService();

        // Call Paylink to add additional information
        $response = $partnerService->addInfo(
            signature: $request->input('signature'),
            sessionUuid: $request->input('sessionUuid'),
            mobile: $request->input('mobile'),
            partnerProfileNo: $request->input('partnerProfileNo'),
            iban: $request->input('iban'),
            bankName: $request->input('bankName'),
            categoryDescription: $request->input('categoryDescription'),
            salesVolume: $request->input('salesVolume'),
            sellingScope: $request->input('sellingScope'), // global or domestic
            nationalId: $request->input('nationalId'),
            licenseName: $request->input('licenseName'),
            Email: $request->input('Email'),
            firstName: $request->input('firstName'),
            lastName: $request->input('lastName'),
            password: $request->input('password'),
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Fourth step: Confirms partner registration with Nafath
    public function confirmingWithNafath(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'signature' => 'required|string',
            'sessionUuid' => 'required|string',
            'mobile' => 'required|string',
            'partnerProfileNo' => 'required|string',
        ]);

        // Create an instance of PartnerService
        $partnerService = new PartnerService();

        // Call Paylink to confirm partner registration with Nafath
        $response = $partnerService->confirmingWithNafath(
            signature: $request->input('signature'),
            sessionUuid: $request->input('sessionUuid'),
            mobile: $request->input('mobile'),
            partnerProfileNo: $request->input('partnerProfileNo'),
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }
}
