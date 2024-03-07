<?php

namespace Paylink\Examples;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * request:
     * @param \Illuminate\Http\Request $request
     * The webhook object contains information about the sold order, including:
     *  - amount
     *  - mobile
     *  - merchantEmail
     *  - transactionNo
     *  - merchantOrderNumber
     *  - orderStatus.
     * 
     * response:
     * The paylink system expects to receive 200 HTTP code responses
     * to ensure the webhook reaches the merchant system.
     * Otherwise, Paylink will try and keep sending the same webhook another ten times.
     * @return \Illuminate\Http\JsonResponse
     * 
     * @see https://paylinksa.readme.io/docs/payment-webhook
     */
    public function paymentWebhook(Request $request)
    {
        // getting the request content
        $amount = $request->input('amount');
        $merchantEmail = $request->input('merchantEmail');
        $transactionNo = $request->input('transactionNo');
        $merchantOrderNumber = $request->input('merchantOrderNumber');
        $orderStatus = $request->input('orderStatus');

        /**
         * In this section, utilize the provided request information
         * to update the payment, order, and invoice statuses in your system.
         * 
         * You may want to interact with your database or external services
         * to reflect the changes based on the webhook data received, including:
         *   - Updating payment status
         *   - Modifying order details
         *   - Managing and updating corresponding invoices
         * 
         * Ensure to handle errors gracefully and respond accordingly.
         */


        return response()->json([
            'message' => 'Webhook received and handled successfully.'
        ], 200);
    }

    /** other methods and functions */
}
