## How to Use the PaySuite PHP SDK

The `paysuite-php-sdk` library allows you to process payments quickly and easily using payment methods available in Mozambique, such as Mpesa, eMola, PayPal, and bank transfers.

[🇵🇹 Documentação em Português](docs/README.pt.md)

### Installation

1. Create an account at [Paysuite.tech](https://paysuite.tech) and obtain your access token from the dashboard

2. Install the library using Composer:

```bash
composer require hypertech/paysuite-php-sdk
```

### Basic Usage

First, import and initialize the client with your token:

```php
use Hypertech\Paysuite\Client;
use Hypertech\Paysuite\Exception\ValidationException;
use Hypertech\Paysuite\Exception\PaysuiteException;

$token = "your-access-token";
$client = new Client($token);
```

#### Create a Payment

```php
try {
    $response = $client->createPayment([
        'amount' => '100.50',
        'reference' => 'INV123',
        'description' => 'Invoice payment',
        'return_url' => 'https://yoursite.com/return',
        'callback_url' => 'https://yoursite.com/callback'
    ]);

    if ($response->isSuccessfully()) {
        // Get the checkout URL to redirect the customer
        $checkoutUrl = $response->getCheckoutUrl();
        
        // Get the payment ID for later reference
        $paymentId = $response->getData()['id'];
        
        // Redirect customer to payment page
        header("Location: " . $checkoutUrl);
        exit;
    }
} catch (ValidationException $e) {
    // Handle validation errors
    echo "Validation error: " . $e->getMessage();
} catch (PaysuiteException $e) {
    // Handle API errors
    echo "API error: " . $e->getMessage();
}
```

#### Check Payment Status

```php
try {
    $response = $client->getPayment($paymentId);
    
    if ($response->isSuccessfully()) {
        $status = $response->getData()['status'];
        
        // Check if transaction data is available
        if (isset($response->getData()['transaction'])) {
            $transaction = $response->getData()['transaction'];
            $transactionId = $transaction['transaction_id'];
            $paidAt = $transaction['paid_at'];
        }
    }
} catch (ValidationException $e) {
    echo "Validation error: " . $e->getMessage();
} catch (PaysuiteException $e) {
    echo "API error: " . $e->getMessage();
}
```

### Validation Rules

The SDK implements comprehensive validation for all API requests:

1. **Payment Request Validation**:
   - `amount`: Must be a positive number
   - `reference`: Required string
   - `description`: Required string
   - `return_url`: Must be a valid URL

2. **UUID Validation**:
   - Payment IDs must follow the standard UUID format (8-4-4-4-12 hexadecimal characters)
   - Example: `550e8400-e29b-41d4-a716-446655440000`

### Response Handling

The `Response` class provides convenient methods to access payment data:

```php
// Check if the request was successful
$isSuccess = $response->isSuccessfully();

// Get the full response content
$content = $response->getContent();

// Get just the data portion of the response
$data = $response->getData();

// Get specific fields with helper methods
$reference = $response->getReference();
$amount = $response->getAmount();
$checkoutUrl = $response->getCheckoutUrl();

// Get error message if request failed
$errorMessage = $response->getMessage();
```

### Error Handling

The SDK includes two main types of exceptions:

- `ValidationException`: For input validation errors (missing fields, invalid values)
- `PaysuiteException`: For API errors (authentication, server errors, etc.)

Error responses include:
- HTTP 400: Bad Request (validation errors)
- HTTP 401: Unauthorized (invalid token)
- HTTP 404: Not Found (invalid payment ID)
- HTTP 500: Server Error

### Running Tests

Configure your test token in the `phpunit.xml` file or via environment variable:

```bash
export TOKEN="your-test-token"
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more details.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for more details.

### Security

If you discover any security-related issues, please email security@hypertech.co.mz instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.