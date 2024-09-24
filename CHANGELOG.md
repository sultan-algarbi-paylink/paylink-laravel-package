## 2.0.1

### Method Renaming:

- `_authentication()` renamed to `authentication()` to follow standard naming conventions.

### Response Error Handling:

- Introduced `handleResponseError()` method to streamline error handling and provide more detailed error messages from API responses.

### Code Cleanup:

- Changed variable names for headers from `$requestHeader` to `$requestHeaders` for consistency.
- Removed redundant or unnecessary code, improving clarity and structure.

### Improved Exception Messages:

- Added more specific exception messages, especially for missing authentication tokens in the response.

## 2.0.0

### Added

- Introduced `PaylinkService` class for a more streamlined interaction with the payment gateway.
- Created environment-specific instances (`test()` and `production()`) for both **PaylinkService** and **PartnerService**.

### Changed

- Refactored **Merchant Service** and **Payment Service** into a unified **PaylinkService**.
- Updated method calls to use new class structure:

  - `addInvoice()`, `getInvoice()`, and `cancelInvoice()` now called from `PaylinkService` instance.
  - Replaced manual environment variable setup with dedicated test and production methods for service initialization.

- Simplified invoice creation by using `PaylinkProduct` model for product details.

### Removed

- Removed the direct use of environment variables in `.env` file for merchant and partner services. Now handled via service initialization methods.

- Deprecated the old **MerchantService** and **PartnerService** environment variable setup approach.

### Documentation

- Updated documentation examples to reflect new service usage and initialization process.
- Replaced old merchant and partner service examples with new PaylinkService and PartnerService usage examples.

## 1.0.6

- Resolved the APP ID error by replacing it with the correct API ID.

## 1.0.5

- Updated README file with detailed examples for each function in the package, providing clear usage instructions and showcasing the functionality.

## 1.0.4

- Implemented robust error handlers to effectively manage various scenarios.
- Enhanced error messages for improved understanding and troubleshooting.
- Updated documentation with clearer examples.

## 1.0.3

- Resolved the products bug in direct invoices.

## 1.0.2

- Corrected the version number in the composer file.
- Added the license.
- Fixed namespaces.

## 1.0.1

- Improved package documentation for enhanced clarity and usability.
- Fixed the Callback URL bug.

## 1.0.0

- Released the initial version of the Paylink Payment Package with essential features.
