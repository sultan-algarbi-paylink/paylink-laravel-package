<?php

namespace Paylink\Examples;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    /**
     * request:
     * @param \Illuminate\Http\Request $request
     * The webhook object contains all relevant information for the settlement, including:
     *  - profileNo
     *  - email
     *  - mobile
     *  - civilId
     *  - licenseType
     *  - licenseName
     *  - licenseNumber
     *  - status
     *  - errorMsg
     * 
     * response:
     * The paylink system expects to receive 200 HTTP code responses
     * to ensure the webhook reaches the partner system.
     * Otherwise, Paylink will try and keep sending the same webhook another ten times.
     * @return \Illuminate\Http\JsonResponse
     * 
     * @see https://paylinksa.readme.io/docs/partner-activation-webhook
     */
    public function activationWebhook(Request $request)
    {
        // getting the request content
        $profileNo = $request->input('profileNo');
        $email = $request->input('email');
        $mobile = $request->input('mobile');
        $civilId = $request->input('civilId');
        $licenseType = $request->input('licenseType');
        $licenseName = $request->input('licenseName');
        $licenseNumber = $request->input('licenseNumber');
        $status = $request->input('status');
        $errorMsg = $request->input('errorMsg');

        /**
         * In this section, utilize the provided request information
         * to update the merchant status and information in your system.
         * 
         * You may want to interact with your database or external services
         * to reflect the changes based on the webhook data received
         * 
         * Ensure to handle errors gracefully and respond accordingly.
         */


        return response()->json([
            'message' => 'Webhook received and handled successfully.'
        ], 200);
    }

    /** other methods and functions */
}
