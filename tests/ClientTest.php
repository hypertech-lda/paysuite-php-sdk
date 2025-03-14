<?php

namespace Hypertech\Paysuite\Tests;

use Hypertech\Paysuite\Client;
use Hypertech\Paysuite\Exception\ValidationException;
use Hypertech\Paysuite\Exception\PaysuiteException;
use Hypertech\Paysuite\Message\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private string $token;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $_ENV['TOKEN'] ?? '';
        
        // Skip tests if no token is provided
        if (empty($this->token)) {
            $this->markTestSkipped('No token provided. Set TOKEN environment variable to run tests.');
        }
        
        $this->client = new Client($this->token);
    }

    public function testClientInitialization(): void
    {
        $client = new Client($this->token);
        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals($this->token, $client->getToken());
    }

    public function testClientInitializationWithEmptyToken(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token cannot be empty');
        new Client('');
    }

    public function testSetToken(): void
    {
        $newToken = 'new-token-' . uniqid();
        $this->client->setToken($newToken);
        $this->assertEquals($newToken, $this->client->getToken());
    }

    public function testSetEmptyToken(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token cannot be empty');
        $this->client->setToken('');
    }

    public function testCreatePaymentValidation(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field: amount');
        
        $this->client->createPayment([
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentMissingReference(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field: reference');
        
        $this->client->createPayment([
            'amount' => '100',
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentMissingDescription(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field: description');
        
        $this->client->createPayment([
            'amount' => '100',
            'reference' => 'TEST123',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentMissingReturnUrl(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field: return_url');
        
        $this->client->createPayment([
            'amount' => '100',
            'reference' => 'TEST123',
            'description' => 'Test Payment'
        ]);
    }

    public function testCreatePaymentInvalidAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount must be a positive number');
        
        $this->client->createPayment([
            'amount' => '-100',
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentZeroAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field: amount');
        
        $this->client->createPayment([
            'amount' => '0', // In PHP, empty('0') is true, so this will trigger the "Missing required field" validation
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentNonEmptyZeroAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount must be a positive number');
        
        $this->client->createPayment([
            'amount' => '0.00', // This won't be caught by empty() but will fail the positive number check
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentInvalidReturnUrl(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid return URL');
        
        $this->client->createPayment([
            'amount' => '100',
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'invalid-url'
        ]);
    }

    public function testGetPaymentInvalidUuid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid UUID format');
        
        $this->client->getPayment('invalid-uuid');
    }

    public function testGetPaymentEmptyUuid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid UUID format');
        
        $this->client->getPayment('');
    }

    public function testGetPaymentMalformedUuid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid UUID format');
        
        // UUID with incorrect format (missing a section)
        $this->client->getPayment('550e8400-e29b-41d4-a716');
    }

    public function testResponseClass(): void
    {
        // Create a mock response
        $responseData = [
            'status' => 'success',
            'data' => [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'amount' => 100.50,
                'reference' => 'TEST1234',
                'status' => 'pending',
                'checkout_url' => 'https://paysuite.tech/checkout/550e8400-e29b-41d4-a716-446655440000'
            ]
        ];
        
        $response = new Response(json_encode($responseData));
        
        // Test basic response methods
        $this->assertTrue($response->isSuccessfully());
        $this->assertEquals($responseData, $response->getContent());
        $this->assertEquals($responseData['data'], $response->getData());
        
        // Test helper methods
        $this->assertEquals('TEST1234', $response->getReference());
        $this->assertEquals(100.50, $response->getAmount());
        $this->assertEquals('https://paysuite.tech/checkout/550e8400-e29b-41d4-a716-446655440000', $response->getCheckoutUrl());
    }

    public function testResponseWithError(): void
    {
        // Create a mock error response
        $responseData = [
            'status' => 'error',
            'message' => 'Test error message'
        ];
        
        $response = new Response(json_encode($responseData));
        
        // Test error response methods
        $this->assertFalse($response->isSuccessfully());
        $this->assertEquals($responseData, $response->getContent());
        $this->assertEquals('Test error message', $response->getMessage());
    }

    public function testResponseWithMissingFields(): void
    {
        // Create a response with missing fields
        $responseData = [
            'status' => 'success',
            'data' => [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                // Missing amount, reference, etc.
            ]
        ];
        
        $response = new Response(json_encode($responseData));
        
        // Test null returns for missing fields
        $this->assertNull($response->getReference());
        $this->assertNull($response->getAmount());
        $this->assertNull($response->getCheckoutUrl());
    }

    /**
     * Generate a random reference string of the specified length.
     */
    private function generateReference(int $length): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $result;
    }
}
