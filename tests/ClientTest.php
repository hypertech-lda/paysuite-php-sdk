<?php

namespace Hypertech\Paysuite\Tests;

use Hypertech\Paysuite\Client;
use Hypertech\Paysuite\Exception\ValidationException;
use Hypertech\Paysuite\Exception\PaysuiteException;
use Hypertech\Paysuite\Message\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private string $token;
    /** @var Client&MockObject */
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $_ENV['TOKEN'];
        $this->client = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([$this->token])
            ->onlyMethods(['request'])
            ->getMock();
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

    public function testCreatePaymentRequest(): void
    {
        $data = [
            'amount' => '100.50',
            'reference' => 'TEST' . $this->generateReference(4),
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ];

        $mockResponse = json_encode([
            'status' => 'success',
            'data' => [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'amount' => 100.50,
                'reference' => $data['reference'],
                'status' => 'pending',
                'checkout_url' => 'https://paysuite.tech/checkout/550e8400-e29b-41d4-a716-446655440000'
            ]
        ]);

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', 'payment-requests', $data)
            ->willReturn($mockResponse);

        $response = $this->client->createPaymentRequest($data);
        
        $this->assertTrue($response->isSuccessfully());
        $responseData = $response->getData();
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('checkout_url', $responseData);
        $this->assertStringStartsWith('https://', $responseData['checkout_url']);
    }

    public function testCreatePaymentRequestValidation(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field: amount');
        
        $this->client->createPaymentRequest([
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentRequestInvalidAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount must be a positive number');
        
        $this->client->createPaymentRequest([
            'amount' => '-100',
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'https://example.com/return'
        ]);
    }

    public function testCreatePaymentRequestInvalidReturnUrl(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid return URL');
        
        $this->client->createPaymentRequest([
            'amount' => '100',
            'reference' => 'TEST123',
            'description' => 'Test Payment',
            'return_url' => 'invalid-url'
        ]);
    }

    public function testGetPaymentRequest(): void
    {
        $paymentId = '550e8400-e29b-41d4-a716-446655440000';
        
        $mockResponse = json_encode([
            'status' => 'success',
            'data' => [
                'id' => $paymentId,
                'amount' => 100.50,
                'reference' => 'TEST1234',
                'status' => 'paid',
                'transaction' => [
                    'id' => 1,
                    'status' => 'completed',
                    'transaction_id' => 'MPESA123456',
                    'paid_at' => '2024-02-10T10:15:00.000000Z'
                ]
            ]
        ]);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', "payment-requests/{$paymentId}")
            ->willReturn($mockResponse);

        $response = $this->client->getPaymentRequest($paymentId);
        
        $this->assertTrue($response->isSuccessfully());
        $responseData = $response->getData();
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals($paymentId, $responseData['id']);
    }

    public function testGetPaymentRequestInvalidUuid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid UUID format');
        
        $this->client->getPaymentRequest('invalid-uuid');
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
