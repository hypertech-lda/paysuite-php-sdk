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

#### Create a Payment Request

```php
try {
    $response = $client->createPaymentRequest([
        'amount' => '100.50',
        'reference' => 'INV123',
        'description' => 'Invoice payment',
        'return_url' => 'https://yoursite.com/return'
    ]);

    if ($response->isSuccessfully()) {
        $data = $response->getData();
        $checkoutUrl = $data['checkout_url'];
        $paymentId = $data['id'];
        
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
    $response = $client->getPaymentRequest($paymentId);
    
    if ($response->isSuccessfully()) {
        $data = $response->getData();
        $status = $data['status'];
        
        if (isset($data['transaction'])) {
            $transactionId = $data['transaction']['transaction_id'];
            $paidAt = $data['transaction']['paid_at'];
        }
    }
} catch (ValidationException $e) {
    echo "Validation error: " . $e->getMessage();
} catch (PaysuiteException $e) {
    echo "API error: " . $e->getMessage();
}
```

### Error Handling

The SDK includes two main types of exceptions:

- `ValidationException`: For validation errors (invalid or missing data)
- `PaysuiteException`: For API errors (authentication, server, etc.)

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