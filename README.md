# Paylink SDK for Laravel

The Paylink SDK provides a set of classes and services to facilitate integration with the Paylink payment system. This README.md file serves as a guide to understand the structure and functionality of the SDK.

## 1. Installation

To integrate the Paylink SDK into your Laravel project, follow these steps:

### Overview:

Before proceeding with the installation, it's essential to understand the two primary setups: **Merchant Setup** and **Partner Setup**. Depending on your role and requirements, you'll need to configure the SDK accordingly.

### Merchant Setup:

1.
2. Add the following environment variables to your `.env` file:

```dotenv
# PRODUCTION ENVIRONMENT:
PAYLINK_PRODUCTION_APP_ID=[your_production_api_id]
PAYLINK_PRODUCTION_SECRET_KEY=[your_production_secret_key]
PAYLINK_PRODUCTION_PERSIST_TOKEN=false
```

4. Replace placeholders as following:

   - `[your_production_api_id]` => `API ID`
   - `[your_production_secret_key]` => `API Secret Key`

   `API ID` and `API Secret Key` can be obtained from [MY PAYLINK PORTAL->SETTINGS](https://my.paylink.sa/).

### Partner Setup:

1. Save the entire `PaylinkSDK` directory in the root directory of your Laravel project.
2. Move the `paylink.php` file from the `/PaylinkSDK` directory to the `/config` directory in your Laravel project.
3. Add the following environment variables to your `.env` file:

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

4. Replace placeholders as following:

   - `[your_profile_no_for_testing]` => `Profile No`
   - `[your_api_key_for_testing]` => `API Key`
   - `[your_profile_no_for_production]` => `Profile No`
   - `[your_api_key_for_production]` => `API Key`

   `Profile No` and `API Key` can be obtained from [MY PAYLINK PORTAL->SETTINGS](https://my.paylink.sa/).

By following these setup instructions, you'll be ready to use the Paylink SDK in your Laravel project, whether you're a merchant or a partner.

## 3. Usage

Now you can start using the Paylink SDK services in your Laravel project. The configuration and credentials are set up, and you can integrate Paylink functionality into your application as needed.

**Here are the steps you should follow after integrating Paylink functionality:**

1. **Testing Integration**: Before deploying your application to production, thoroughly test the Paylink integration to ensure it functions as expected. Test various scenarios, including successful payments, failed payments, and edge cases.

2. **Error Handling**: Implement robust error handling mechanisms to gracefully handle any errors or exceptions that may occur during payment processing. This includes handling network errors, API timeouts, and validation errors.

3. **Security Considerations**: Pay attention to security best practices to protect sensitive data, such as API credentials and customer information. Utilize HTTPS for all communication with the Paylink API and implement encryption where necessary.

4. **Monitoring and Logging**: Set up monitoring and logging to track the performance of your Paylink integration and detect any issues or anomalies. Monitor API usage, response times, and error rates to ensure optimal performance.

For further information on how to use the Paylink SDK, refer to the official documentation or examples provided in the SDK directory.

For any issues or additional information, please refer to the [Paylink Documentation](https://developer.paylink.sa/) or contact PayLink support.

## 4. Contributing

Contributions to the Paylink SDK are welcome! If you find any issues or have suggestions for improvements, please feel free to contact PayLink support.
