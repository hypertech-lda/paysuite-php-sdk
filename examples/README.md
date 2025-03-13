# PaySuite PHP SDK Examples

This directory contains example scripts to help you test and understand the PaySuite PHP SDK.

## Running the Test Script

```bash
# Run the test script
php test-sdk.php
```

## Configuration

Before running the script, set your PaySuite token:

```bash
# Using environment variable
export PAYSUITE_TOKEN="your-token-here"

# Or edit the script and set your token directly:
$token = "your-token-here";
```

## What the Script Does

The script demonstrates:
1. Creating a payment request
2. Getting the checkout URL
3. Checking payment status
4. Response validation
5. Error handling

## Response Validation

The script implements robust response validation:

### Helper Function
```php
validateResponseData($data, $requiredFields, $context)
```
- Validates response data structure
- Checks for required fields
- Provides context-specific error messages

### Payment Request Response
```php
{
  "status": "success",
  "data": {
    "id": "uuid",          // Required
    "amount": "10.00",     // Required
    "reference": "TEST1234", // Required
    "status": "pending",   // Required
    "checkout_url": "https://..." // Required
  }
}
```

### Payment Status Response
```php
{
  "status": "success",
  "data": {
    "status": "pending|paid", // Required
    "transaction": {          // Optional
      "id": "123",           // Required if present
      "status": "completed", // Required if present
      "transaction_id": "mpesa_123", // Required if present
      "paid_at": "2025-03-13 02:45:00" // Required if present
    }
  }
}
```

## Error Handling

The script handles various error types:
- Validation errors (invalid input data)
- API errors (authentication, server issues)
- Response format errors:
  - Missing required fields
  - Invalid data types
  - Malformed responses
- Network issues

## Example Output

Successful payment request:
```
=== PaySuite SDK Test ===

1. Creating payment request...

✓ Payment request created:
------------------------
Payment ID:    550e8400-e29b-41d4-a716-446655440000
Amount:        10.00 MZN
Reference:     TEST1234
Status:        pending
Checkout URL:  https://paysuite.tech/checkout/...

2. Checking payment status...

✓ Payment status:
----------------
Current status: pending

ℹ No transaction details yet
To complete payment, visit:
https://paysuite.tech/checkout/...

=== Test Complete ===
```

Validation error example:
```
=== PaySuite SDK Test ===

1. Creating payment request...

✗ API error:
Invalid payment request response: missing required fields: id, status

=== Test Complete ===
```

For more details, check the [main documentation](../README.md).
