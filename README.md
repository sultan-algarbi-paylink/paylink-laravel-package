# Paylink Package

This package enables seamless integration with the Paylink payment gateway within Laravel applications. and provides convenient methods to interact with the Paylink API, facilitating payment processing and related functionalities.

## Installation

You can install the `paylinksa/laravel` package via composer. Run the following command in your terminal:

```bash
composer require paylinksa/laravel
```

## Payment Service

### Environment Setup

Create an instance of PaylinkService based on your environment

- For Testing

```php
use Paylink\Services\PaylinkService;

$paylinkService = PaylinkService::test();
```

- For Production

```php
use Paylink\Services\PaylinkService;

$paylinkService = PaylinkService::production('API_ID_xxxxxxxxxx', 'SECRET_KEY_xxxxxxxxxx');
```

### Methods

1. **Add Invoice**:

   Add an invoice to the system for payment processing.

   ```php
      use Paylink\Models\PaylinkProduct;

      $invoiceDetails = $paylinkService->addInvoice(
         amount: 250.0,
         clientMobile: '0512345678',
         clientName: 'Mohammed Ali',
         orderNumber: '123456789',
         products: [
            new PaylinkProduct(title: 'item1', price: 5.0, qty: 10),
            new PaylinkProduct(title: 'item2', price: 20.0, qty: 10)
         ],
         callBackUrl: 'https://example.com',
      );
   ```

2. **Get Invoice**

   Retrieve invoice details.

   ```php
      $invoiceDetails = $paylinkService->getInvoice(transactionNo: '1714289084591');

      // $invoiceDetails->orderStatus;
      // $invoiceDetails->transactionNo;
      // $invoiceDetails->url;
      // ...
   ```

3. **Cancel Invoice**

   Cancel an existing invoice initiated by the merchant.

   ```php
      $paylinkService->cancelInvoice(transactionNo: '1714289084591'); // true-false
   ```

### Examples:

- [Paylink Payment Examples](Examples/PaymentExamples.php)
- [Paylink Payment Webhook](Examples/PaymentWebhook.php) (used by merchants)

For detailed usage instructions, refer to the [Paylink Payment Services Documentation](docs/PaylinkService.md)

---

## Partner Service

### Environment Setup

Create an instance of PartnerService based on your environment

- For Testing

```php
use Paylink\Services\PartnerService;

$partnerService = PartnerService::test('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');
```

- For Production

```php
use Paylink\Services\PartnerService;

$partnerService = PartnerService::production('profileNo_xxxxxxxxxxx', 'apiKey_xxxxxxxxxxxx');
```

### Methods

1. **Check License**

   Initiates the first step of the registration process by checking the merchant's license information.

   ```php
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
      $responseData = $partnerService->getMyMerchants();
   ```

6. **Get Merchant Keys**

   Retrieves API credentials (API ID and Secret Key) for a specific sub-merchant.

   ```php
      $responseData = $partnerService->getMerchantKeys(
         searchType: 'cr', // cr, freelancer, mobile, email, accountNo
         searchValue: '20139202930',
         profileNo: '12345687',
      );
   ```

### Examples:

- [Partner Examples](Examples/PartnerExamples.php)
- [Activation Webhook](Examples/ActivationWebhook.php) (used by partners)

For detailed usage instructions, refer to the [Partner Service Documentation](docs/PartnerService.md)

---

## Support

If you encounter any issues or have questions about the Paylink Package, please [contact us](https://paylink.sa/).

## License

This package is open-source software licensed under the [MIT license](LICENSE).
