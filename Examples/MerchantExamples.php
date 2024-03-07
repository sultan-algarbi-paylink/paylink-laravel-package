<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Import Paylink Package
use Paylink\Services\MerchantService;
use Paylink\Models\PaylinkProduct;

class MerchantController extends Controller
{

    // Adds a new invoice
    public function addInvoice(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'amount' => 'required|numeric',
            'clientMobile' => 'required|string',
            'clientName' => 'required|string',
            'orderNumber' => 'required|string',
            'products' => 'required|array',
            'products.*.title' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.qty' => 'required|numeric',
            'products.*.description' => 'nullable|string',
            'products.*.isDigital' => 'nullable|boolean',
            'products.*.imageSrc' => 'nullable|string',
            'products.*.specificVat' => 'nullable|numeric',
            'products.*.productCost' => 'nullable|numeric',
            'callBackUrl' => 'required|url',
            'cancelUrl' => 'nullable|url',
            'clientEmail' => 'nullable|email',
            'currency' => 'nullable|string',
            'note' => 'nullable|string',
            'smsMessage' => 'nullable|string',
            'supportedCardBrands' => 'nullable|array',
            'supportedCardBrands.*' => 'nullable|string',
            'displayPending' => 'nullable|boolean',
        ]);

        // Create an instance of MerchantService
        $merchantService = new MerchantService();

        // Prepare products as PaylinkProduct objects
        $products = [];
        if ($request->has('products')) {

            // option 1:
            foreach ($request->input('products') as $item) {
                // Create PaylinkProduct object for each product
                $product = new PaylinkProduct(
                    title: $item['title'],
                    price: $item['price'],
                    qty: $item['qty'],
                    description: $item['description'] ?? null, // optional
                    isDigital: $item['isDigital'] ?? null, // optional
                    imageSrc: $item['imageSrc'] ?? null, // optional
                    specificVat: $item['specificVat'] ?? null, // optional
                    productCost: $item['productCost'] ?? null, // optional
                );
                $products[] = $product;
            }

            // option 2
            // $items = $request->input('products');
            // // update the value of each key-value pair based on your items keys
            // $keyMap = [
            //     'title' => 'title',
            //     'price' => 'price',
            //     'qty' => 'qty',
            //     'description' => 'description',
            //     'isDigital' => 'isDigital',
            //     'imageSrc' => 'imageSrc',
            //     'specificVat' => 'specificVat',
            //     'productCost' => 'productCost',
            // ];
            // $products = PaylinkProduct::createFromItems($items, $keyMap);
        }

        // Call Paylink to add a new invoice
        $response = $merchantService->addInvoice(
            amount: $request->input('amount'),
            clientMobile: $request->input('clientMobile'),
            clientName: $request->input('clientName'),
            orderNumber: $request->input('orderNumber'),
            products: $products,
            callBackUrl: $request->input('callBackUrl'),
            cancelUrl: $request->input('cancelUrl'), // optional
            clientEmail: $request->input('clientEmail'), // optional
            currency: $request->input('currency'), // optional
            note: $request->input('note'), // optional
            smsMessage: $request->input('smsMessage'), // optional
            supportedCardBrands: $request->input('supportedCardBrands'), // optional
            displayPending: $request->input('displayPending'), // optional
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Pay Invoices (Direct Integration)
    public function processPaymentWithCardInformation(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'amount' => 'required|numeric',
            'clientMobile' => 'required|string',
            'clientName' => 'required|string',
            'orderNumber' => 'required|string',
            'products' => 'required|array',
            'products.*.title' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.qty' => 'required|numeric',
            'products.*.description' => 'nullable|string',
            'products.*.isDigital' => 'nullable|boolean',
            'products.*.imageSrc' => 'nullable|string',
            'products.*.specificVat' => 'nullable|numeric',
            'products.*.productCost' => 'nullable|numeric',
            'cardNumber' => 'required|string',
            'cardSecurityCode' => 'required|string',
            'cardExpiryMonth' => 'required|string',
            'cardExpiryYear' => 'required|string',
            'callBackUrl' => 'required|url',
            'cancelUrl' => 'nullable|url',
            'clientEmail' => 'nullable|email',
            'currency' => 'nullable|string',
            'note' => 'nullable|string',
            'smsMessage' => 'nullable|string',
            'supportedCardBrands' => 'nullable|array',
            'supportedCardBrands.*' => 'nullable|string',
            'displayPending' => 'nullable|boolean',
        ]);

        // Create an instance of MerchantService
        $merchantService = new MerchantService();

        // Prepare products as PaylinkProduct objects
        $products = [];
        if ($request->has('products')) {
            foreach ($request->input('products') as $item) {
                $product = new PaylinkProduct(
                    title: $item['title'],
                    price: $item['price'],
                    qty: $item['qty'],
                    description: $item['description'] ?? null, // optional
                    isDigital: $item['isDigital'] ?? null, // optional
                    imageSrc: $item['imageSrc'] ?? null, // optional
                    specificVat: $item['specificVat'] ?? null, // optional
                    productCost: $item['productCost'] ?? null, // optional
                );
                $products[] = $product;
            }
        }

        // calling paylink to pay Invoice
        $response = $merchantService->processPaymentWithCardInformation(
            amount: $request->input('amount'),
            clientMobile: $request->input('clientMobile'),
            clientName: $request->input('clientName'),
            orderNumber: $request->input('orderNumber'),
            products: $products,
            cardNumber: $request->input('cardNumber'),
            cardSecurityCode: $request->input('cardSecurityCode'),
            cardExpiryMonth: $request->input('cardExpiryMonth'),
            cardExpiryYear: $request->input('cardExpiryYear'),
            callBackUrl: $request->input('callBackUrl'),
            cancelUrl: $request->input('cancelUrl'), // optional
            clientEmail: $request->input('clientEmail'), // optional
            currency: $request->input('currency'), // optional
            note: $request->input('note'), // optional
            smsMessage: $request->input('smsMessage'), // optional
            supportedCardBrands: $request->input('supportedCardBrands'), // optional
            displayPending: $request->input('displayPending'), // optional
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Initiates a recurring payment
    public function recurringPayment(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'paymentValue' => 'required|numeric',
            'customerName' => 'required|string',
            'customerMobile' => 'required|string',
            'recurringType' => 'required|string|in:Custom,Daily,Weekly,Monthly',
            'recurringIntervalDays' => 'required|numeric|min:1|max:180',
            'recurringIterations' => 'required|numeric',
            'recurringRetryCount' => 'required|numeric|min:0|max:5',
            'callbackUrl' => 'required|url',
            'currencyCode' => 'nullable|string',
            'customerEmail' => 'nullable|email',
            'paymentNote' => 'nullable|string',
        ]);

        // Create an instance of MerchantService
        $merchantService = new MerchantService();

        // Call Paylink to initiate a recurring payment
        $response = $merchantService->recurringPayment(
            paymentValue: $request->input('paymentValue'),
            customerName: $request->input('customerName'),
            customerMobile: $request->input('customerMobile'),
            recurringType: $request->input('recurringType'),
            recurringIntervalDays: $request->input('recurringIntervalDays'),
            recurringIterations: $request->input('recurringIterations'),
            recurringRetryCount: $request->input('recurringRetryCount'),
            callbackUrl: $request->input('callbackUrl'),
            currencyCode: $request->input('currencyCode'), // optional
            customerEmail: $request->input('customerEmail'), // optional
            paymentNote: $request->input('paymentNote'), // optional
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Retrieves an invoice
    public function getInvoice(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'transactionNo' => 'required|string',
        ]);

        // Create an instance of MerchantService
        $merchantService = new MerchantService();

        // Call Paylink to get the invoice
        $response = $merchantService->getInvoice(
            transactionNo: $request->input('transactionNo')
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Cancels an invoice
    public function cancelInvoice(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'transactionNo' => 'required|string',
        ]);

        // Create an instance of MerchantService
        $merchantService = new MerchantService();

        // Call Paylink to cancel the invoice
        $response = $merchantService->cancelInvoice(
            transactionNo: $request->input('transactionNo')
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }

    // Sends a digital product
    public function sendDigitalProduct(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'message' => 'required|string',
            'orderNumber' => 'required|string',
        ]);

        // Create an instance of MerchantService
        $merchantService = new MerchantService();

        // Call Paylink to send the digital product
        $response = $merchantService->sendDigitalProduct(
            message: $request->input('message'),
            orderNumber: $request->input('orderNumber'),
        );

        // Return response as JSON
        return response()->json($response->json(), $response->status());
    }
}
