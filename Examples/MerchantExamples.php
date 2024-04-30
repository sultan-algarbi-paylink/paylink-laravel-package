<?php

namespace Paylink\Examples;

use App\Http\Controllers\Controller;
use Exception;

// Import Paylink Package
use Paylink\Services\MerchantService;
use Paylink\Models\PaylinkProduct;

class MerchantController extends Controller
{
    /** -------------------------------- Invoice Operations -------------------------------- */
    // Adds a new invoice
    public function addInvoice()
    {
        try {
            // Create an instance of MerchantService
            $merchantService = new MerchantService();

            // Prepare products as PaylinkProduct objects
            $products = [
                new PaylinkProduct(
                    title: 'Book',
                    price: 50.0,
                    qty: 2,
                    description: null, // optional
                    isDigital: false, // optional
                    imageSrc: null, // optional
                    specificVat: null, // optional
                    productCost: null, // optional
                ),
                new PaylinkProduct(
                    title: 'Pen',
                    price: 7.0,
                    qty: 10,
                )
            ];

            // Call Paylink to add a new invoice
            $invoiceDetails = $merchantService->addInvoice(
                amount: 170.0,
                clientMobile: '0512345678',
                clientName: 'Mohammed Ali',
                orderNumber: '123456789',
                products: $products,
                callBackUrl: 'https://example.com',
                cancelUrl: 'https://example.com', // optional
                clientEmail: 'mohammed@test.com', // optional
                currency: 'SAR', // optional
                note: 'Test invoice', // optional
                smsMessage: 'URL: [SHORT_URL], Amount: [AMOUNT]', // optional
                supportedCardBrands: ['mada', 'visaMastercard', 'amex', 'tabby', 'tamara', 'stcpay', 'urpay'], // optional
                displayPending: true, // optional
            );

            // -- Use the invoiceDetails based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Retrieves an invoice
    public function getInvoice()
    {
        try {
            // Create an instance of MerchantService
            $merchantService = new MerchantService();

            // Call Paylink to get the invoice
            $invoiceDetails = $merchantService->getInvoice(transactionNo: '1714289084591');

            // -- Use the invoiceDetails based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Cancels an invoice
    public function cancelInvoice()
    {
        try {
            // Create an instance of MerchantService
            $merchantService = new MerchantService();

            // Call Paylink to cancel the invoice
            $merchantService->cancelInvoice(
                transactionNo: '1714289084591'
            );

            // -- If no error exception is thrown, the invoice was canceled successfully
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    /** -------------------------------- Extra Functions -------------------------------- */

    // Pay Invoices (Direct Integration)
    public function processPaymentWithCardInfo()
    {
        try {
            // Create an instance of MerchantService
            $merchantService = new MerchantService();

            // Prepare products as PaylinkProduct objects
            $products = [
                new PaylinkProduct(
                    title: 'Book',
                    price: 50.0,
                    qty: 2,
                    description: null, // optional
                    isDigital: false, // optional
                    imageSrc: null, // optional
                    specificVat: null, // optional
                    productCost: null, // optional
                ),
                new PaylinkProduct(
                    title: 'Pen',
                    price: 7.0,
                    qty: 10,
                )
            ];

            // calling paylink to pay Invoice
            $responseData = $merchantService->processPaymentWithCardInfo(
                // Card Info
                cardNumber: '4242424242424242',
                cardSecurityCode: '123',
                cardExpiryMonth: '10',
                cardExpiryYear: '35',
                // Order Info
                amount: 170.0,
                clientMobile: '0512345678',
                clientName: 'Mohammed Ali',
                orderNumber: '123456789',
                products: $products,
                callBackUrl: 'https://example.com',
                cancelUrl: 'https://example.com', // optional
                clientEmail: 'mohammed@test.com', // optional
                currency: 'SAR', // optional
                note: 'Test invoice', // optional
                smsMessage: 'URL: [SHORT_URL], Amount: [AMOUNT]', // optional
                supportedCardBrands: ['mada', 'visaMastercard', 'amex', 'tabby', 'tamara', 'stcpay', 'urpay'], // optional
                displayPending: true, // optional
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }

    // Initiates a recurring payment
    public function recurringPayment()
    {
        try {
            // Create an instance of MerchantService
            $merchantService = new MerchantService();

            // Call Paylink to initiate a recurring payment
            $responseData = $merchantService->recurringPayment(
                paymentValue: 1000.0,
                customerName: 'Mohammed Ali',
                customerMobile: '0512345678',
                recurringType: 'Custom',
                recurringIntervalDays: 5,
                recurringIterations: 30,
                recurringRetryCount: 3,
                callbackUrl: 'https://example.com',
                currencyCode: 'SAR', // optional
                customerEmail: 'mohammed@test.com', // optional
                paymentNote: 'any payment notes', // optional
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }


    // Sends a digital product
    public function sendDigitalProduct()
    {
        try {
            // Create an instance of MerchantService
            $merchantService = new MerchantService();

            // Call Paylink to send the digital product
            $responseData = $merchantService->sendDigitalProduct(
                message: 'CODE: 12112AAADDB11',
                orderNumber: '123456789',
            );

            // -- Use the responseData based on your need
        } catch (Exception $e) {
            // -- Handle the error
        }
    }
}
