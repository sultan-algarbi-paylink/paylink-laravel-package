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

3. Save the changes to the `config/app.php` file.

## Merchant & Partner Configuration

Before proceeding with this configuration, it's essential to understand the two primary setups: **Merchant Setup** and **Partner Setup**. Depending on your role and requirements, you'll need to configure your project environment accordingly.

### Merchant Setup:

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

### Partner Setup:

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

## Usage

You can now use the Paylink package within your Laravel application. Import the necessary classes and start using the provided methods to interact with the Paylink API.

- [Merchant Service](docs/MerchantService.md)
- [Partner Service](docs/PartnerService.md)

### Examples:

- [Merchant Examples](Examples/MerchantExamples.php)
- [Partner Examples](Examples/PartnerExamples.php)
- [Activation Webhook](Examples/ActivationWebhook.php) (used by partners)
- [Payment Webhook](Examples/PaymentWebhook.php) (used by merchants)

For detailed usage instructions, refer to the [official Paylink API documentation](https://developer.paylink.sa/).

## Support

If you encounter any issues or have questions about the Paylink Package, please [contact us](https://paylink.sa/).

## License

This package is open-source software licensed under the [MIT license](LICENSE).
