### PaylinkService

The `PaylinkService` class provides methods to interact with the Paylink API for merchant-related operations. It offers functionalities such as adding invoices, processing payments with card information, and managing recurring payments.

#### Invoice Operations

1. **Add Invoice**

   - **Method:** `addInvoice(...)`
   - **Purpose:** Adds an invoice to the system for payment processing.
   - **Endpoint Reference:** [Add Invoice](https://paylinksa.readme.io/docs/invoices)

2. **Get Invoice**

   - **Method:** `getInvoice(string $transactionNo)`
   - **Purpose:** Retrieves the payment status of an invoice.
   - **Endpoint Reference:** [Get Invoice](https://paylinksa.readme.io/docs/order-request)

3. **Cancel Invoice**

   - **Method:** `cancelInvoice(string $transactionNo)`
   - **Purpose:** Cancels an existing invoice initiated by the merchant.
   - **Endpoint Reference:** [Cancel Invoice](https://paylinksa.readme.io/docs/cancel-invoice)

#### Extra Functions

1. **Process Payment with Card Information**

   - **Method:** `processPaymentWithCardInfo(...)`
   - **Purpose:** Processes a payment using card information provided by the customer.
   - **Endpoint Reference:** [Process Payment with Card Information](https://paylinksa.readme.io/docs/add-invoices-direct)

2. **Recurring Payment**

   - **Method:** `recurringPayment(...)`
   - **Purpose:** Initiates recurring payments for regular transactions.
   - **Endpoint Reference:** [Recurring Payment](https://paylinksa.readme.io/docs/recurring-payment)

3. **Send Digital Product**

   - **Method:** `sendDigitalProduct(string $message, string $orderNumber)`
   - **Purpose:** Sends digital product information to the buyer after payment completion.
   - **Endpoint Reference:** [Send Digital Product](https://paylinksa.readme.io/reference/sendproductinfotopayerusingpost)

For more details about each method and its parameters, refer to the provided endpoint references.
