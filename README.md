# Paylink Package

This package enables seamless integration with the Paylink payment gateway within Laravel applications. and provides convenient methods to interact with the Paylink API, facilitating payment processing and related functionalities.

## Installation

You can install the `paylinksa/laravel` package via composer. Run the following command in your terminal:

```bash
composer require paylinksa/laravel
```

## Package Integration

After installing the package, you need to add the Paylink service provider to your Laravel application configuration.

1. Open your Laravel application's `config/app.php` file.

2. Add the following line to the `providers` array:

   ```php
   'providers' => [
       // Other Service Providers...
       Paylink\PaylinkServiceProvider::class,
   ],
   ```

---

## Merchant Service

### Environment Setup

1. Add the following environment variables to your `.env` file:

```dotenv
# PRODUCTION ENVIRONMENT:
PAYLINK_PRODUCTION_APP_ID=[your_production_api_id]
PAYLINK_PRODUCTION_SECRET_KEY=[your_production_secret_key]
PAYLINK_PRODUCTION_PERSIST_TOKEN=false
```

2. Replace placeholders as following:

   - `[your_production_api_id]` => `API ID`
   - `[your_production_secret_key]` => `API Secret Key`

   `API ID` and `API Secret Key` can be obtained from [MY PAYLINK PORTAL->SETTINGS](https://my.paylink.sa/).

### Methods

1. **Add Invoice**:

   Add an invoice to the system for payment processing.

   ```php
      $merchantService = new MerchantService();
      $invoiceDetails = $merchantService->addInvoice(
         amount: 170.0,
         clientMobile: '0512345678',
         clientName: 'Mohammed Ali',
         orderNumber: '123456789',
         products: $products,
         callBackUrl: 'https://example.com',
      );
   ```

2. **Get Invoice**

   Retrieve invoice details.

   ```php
      $merchantService = new MerchantService();
      $invoiceDetails = $merchantService->getInvoice(transactionNo: '1714289084591');
   ```

3. **Cancel Invoice**

   Cancel an existing invoice initiated by the merchant.

   ```php
      $merchantService = new MerchantService();
      $merchantService->cancelInvoice(transactionNo: '1714289084591');
   ```

### Examples:

- [Merchant Examples](Examples/MerchantExamples.php)
- [Payment Webhook](Examples/PaymentWebhook.php) (used by merchants)

For detailed usage instructions, refer to the [Merchant Services](docs/MerchantService.md)

---

## Partner Service

### Environment Setup

1. Add the following environment variables to your `.env` file:

```dotenv
# TESTING ENVIRONMENT:
PAYLINK_TESTING_PROFILE_NO=[your_profile_no_for_testing]
PAYLINK_TESTING_API_KEY=[your_api_key_for_testing]
PAYLINK_TESTING_PERSIST_TOKEN=false

# PRODUCTION ENVIRONMENT:
PAYLINK_PRODUCTION_PROFILE_NO=[your_profile_no_for_production]
PAYLINK_PRODUCTION_API_KEY=[your_api_key_for_production]
PAYLINK_PRODUCTION_PERSIST_TOKEN=false
```

2. Replace placeholders as following:

   - `[your_profile_no_for_testing]` => `Profile No`
   - `[your_api_key_for_testing]` => `API Key`
   - `[your_profile_no_for_production]` => `Profile No`
   - `[your_api_key_for_production]` => `API Key`

   `Profile No` and `API Key` can be obtained from [MY PAYLINK PORTAL->SETTINGS](https://my.paylink.sa/).

### Methods

1. **Check License**

   Initiates the first step of the registration process by checking the merchant's license information.

   ```php
      $partnerService = new PartnerService();
      $responseData = $partnerService->checkLicense(
         registrationType: "cr", // freelancer or cr
         licenseNumber: "7014832310",
         mobileNumber: "0512345678",
         hijriYear: "1400",
         hijriMonth: "06",
         hijriDay: "16",
         partnerProfileNo: "07537924"
      );
   ```

2. **Validate Mobile**

   Validates the merchant's mobile number by confirming the OTP received via SMS.

   ```php
      $partnerService = new PartnerService();
      $responseData = $partnerService->validateMobile(
         signature: "ae135f2506dc3c44152d62265419c09e80dec0b108090bc81d6a1a691c3f0647",
         mobile: "0512345678",
         sessionUuid: "96ea8e22-edef-414b-9724-3bd2d494b710",
         otp: "7615",
         partnerProfileNo: "19039481"
      );
   ```

3. **Add Information**

   Adds information related to the merchant, such as bank details, business category, and personal information.

   ```php
      $partnerService = new PartnerService();
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
   ```

4. **Confirming Account with Nafath**

   Confirms the account with Nafath after submitting the required information.

   ```php
      $partnerService = new PartnerService();
      $responseData = $partnerService->confirmingWithNafath(
         signature: 'ae135f2506dc3c44152d62265419c09e80dec0b108090bc81d6a1a691c3f0647',
         sessionUuid: '96ea8e22-edef-414b-9724-3bd2d494b710',
         mobile: '0512345678',
         partnerProfileNo: '19039481',
      );
   ```

5. **Get My Merchants**

   Retrieves a list of merchants associated with the partner's account.

   ```php
      $partnerService = new PartnerService();
      $responseData = $partnerService->getMyMerchants();
   ```

6. **Get Merchant Keys**

   Retrieves API credentials (App ID and Secret Key) for a specific sub-merchant.

   ```php
      $partnerService = new PartnerService();
      $responseData = $partnerService->getMerchantKeys(
         searchType: 'cr', // cr, freelancer, mobile, email, accountNo
         searchValue: '20139202930',
         profileNo: '12345687',
      );
   ```

### Examples:

- [Partner Examples](Examples/PartnerExamples.php)
- [Activation Webhook](Examples/ActivationWebhook.php) (used by partners)

For detailed usage instructions, refer to the [Partner Service](docs/PartnerService.md)

---

## Support

If you encounter any issues or have questions about the Paylink Package, please [contact us](https://paylink.sa/).

## License

This package is open-source software licensed under the [MIT license](LICENSE).
