### PartnerService

The `PartnerService` class provides methods to interact with the Paylink API for partner-related operations. It offers functionalities such as retrieving merchant information, archiving merchants, and facilitating the registration process for new merchants.

#### Partner Registration

1. **Check License**

   - **Method:** `checkLicense(...)`
   - **Purpose:** Initiates the first step of the registration process by checking the merchant's license information.
   - **Endpoint Reference:** [Partner Registration API - First Step](https://paylinksa.readme.io/docs/partner-registration-api#first-step-check-license)

2. **Validate Mobile**

   - **Method:** `validateMobile(...)`
   - **Purpose:** Validates the merchant's mobile number by confirming the OTP received via SMS.
   - **Endpoint Reference:** [Partner Registration API - Second Step](https://paylinksa.readme.io/docs/partner-registration-api#second-step-validate-mobile)

3. **Add Information**

   - **Method:** `addInfo(...)`
   - **Purpose:** Adds information related to the merchant, such as bank details, business category, and personal information.
   - **Endpoint Reference:** [Partner Registration API - Third Step](https://paylinksa.readme.io/docs/partner-registration-api#third-step-add-information-for-bank-and-merchant)

4. **Confirming Account with Nafath**
   - **Method:** `confirmingWithNafath(...)`
   - **Purpose:** Confirms the account with Nafath after submitting the required information.
   - **Endpoint Reference:** [Partner Registration API - Fourth Step](https://paylinksa.readme.io/docs/partner-registration-api#fourth-step-confirming-account-with-nafath)

#### Merchant Operations

1. **Get My Merchants**

   - **Method:** `getMyMerchants()`
   - **Purpose:** Retrieves a list of merchants associated with the partner's account.
   - **Endpoint Reference:** [Get My Merchants](https://paylinksa.readme.io/docs/paylink-partner-api-get-my-merchants)

2. **Get Merchant Keys**

   - **Method:** `getMerchantKeys(...)`
   - **Purpose:** Retrieves API credentials (API ID and Secret Key) for a specific sub-merchant.
   - **Endpoint Reference:** [Get Merchant Keys](https://paylinksa.readme.io/docs/paylink-partner-api-get-merchant-keys)

3. **Archive Merchant**
   - **Method:** `archiveMerchant(...)`
   - **Purpose:** Archives a merchant using a specific key, such as a mobile number.
   - **Endpoint Reference:** [Paylink Merchant Archive API](https://paylinksa.readme.io/docs/paylink-merchant-archive-api)

For more details about each method and its parameters, refer to the provided endpoint references.
