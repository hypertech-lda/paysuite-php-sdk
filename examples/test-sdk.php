<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hypertech\Paysuite\Client;
use Hypertech\Paysuite\Exception\ValidationException;
use Hypertech\Paysuite\Exception\PaysuiteException;

/**
 * PaySuite SDK Test Script
 * 
 * This script demonstrates the basic usage of the PaySuite PHP SDK:
 * 1. Creating a payment request
 * 2. Retrieving payment status
 * 3. Handling responses and errors
 * 
 * Expected API Response Format:
 * {
 *   "status": "success",
 *   "data": {
 *     "id": "uuid",
 *     "amount": "10.00",
 *     "reference": "TEST1234",
 *     "status": "pending",
 *     "checkout_url": "https://..."
 *   }
 * }
 */

// Replace with your PaySuite token
$token = getenv('PAYSUITE_TOKEN') ?: 'your-token-here';



try {
    // Initialize the client
    $client = new Client($token);
    
    echo "\n=== PaySuite SDK Test ===\n";
    
    // Create a payment request
    $paymentData = [
        'amount' => '10.00',
        'reference' => 'TEST' . rand(1000, 9999),
        'description' => 'PaySuite SDK Test Payment',
        'return_url' => 'https://example.com/return'
    ];
    
    echo "\n1. Creating payment request...\n";
    echo "Request data:\n";
    echo json_encode($paymentData, JSON_PRETTY_PRINT) . "\n\n";
    
    $response = $client->createPaymentRequest($paymentData);
    
    if (!$response->isSuccessfully()) {
        echo "Error response:\n";
        echo "Status: " . ($response->getContent()['status'] ?? 'error') . "\n";
        echo "Message: " . $response->getMessage() . "\n\n";
        throw new PaysuiteException($response->getMessage());
    }
    
    $data = $response->getData();
    
    echo "\n✓ Payment request created:\n";
    echo "------------------------\n";
    echo "Payment ID:    {$data['id']}\n";
    echo "Amount:        {$data['amount']} MZN\n";
    echo "Reference:     {$data['reference']}\n";
    echo "Checkout URL:  {$data['checkout_url']}\n";
    
    // Check payment status
    echo "\n2. Checking payment status...\n";
    $statusResponse = $client->getPaymentRequest($data['id']);
    
    if (!$statusResponse->isSuccessfully()) {
        echo "Error response:\n";
        echo "Message: " . $statusResponse->getMessage() . "\n\n";
        throw new PaysuiteException($statusResponse->getMessage());
    }
    
    $statusData = $statusResponse->getData();

    echo "\n✓ Payment status:\n";
    echo "----------------\n";
    
    if (isset($statusData['transaction'])) {
        $transaction = $statusData['transaction'];
        echo "\nTransaction details:\n";
        echo "-------------------\n";
        echo "ID:             {$transaction['id']}\n";
        echo "Status:         {$transaction['status']}\n";
        echo "Transaction ID: {$transaction['transaction_id']}\n";
        echo "Paid at:        {$transaction['paid_at']}\n";
    } else {
        echo "\nℹ No transaction details yet\n";
        echo "To complete payment, visit:\n{$data['checkout_url']}\n";
    }
    
} catch (ValidationException $e) {
    echo "\n✗ Validation error:\n";
    echo $e->getMessage() . "\n";
} catch (PaysuiteException $e) {
    echo "\n✗ API error:\n";
    echo $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "\n✗ Unexpected error:\n";
    echo $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
