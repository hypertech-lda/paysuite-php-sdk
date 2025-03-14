<?php

namespace Hypertech\Paysuite;

use Hypertech\Paysuite\Message\Response;
use Hypertech\Paysuite\Exception\PaysuiteException;
use Hypertech\Paysuite\Exception\ValidationException;

class Client
{
    private string $token;
    private string $api_url = "https://paysuite.tech/api/v1";
    private int $timeout = 30;

    /**
     * @param string $token Bearer token for authentication
     * @throws \InvalidArgumentException if token is empty
     */
    public function __construct(string $token)
    {
        if (empty(trim($token))) {
            throw new \InvalidArgumentException('Token cannot be empty');
        }
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @throws \InvalidArgumentException if token is empty
     */
    public function setToken(string $token): void
    {
        if (empty(trim($token))) {
            throw new \InvalidArgumentException('Token cannot be empty');
        }
        $this->token = trim($token);
    }

    /**
     * Creates a new payment request
     * 
     * @param array $data Payment request data (amount, reference, description, return_url)
     * @return Response
     * @throws ValidationException if required fields are missing or invalid
     * @throws PaysuiteException if API request fails
     */
    public function createPayment(array $data): Response
    {
        $this->validatePaymentRequestData($data);
        $result = $this->request('POST', 'payments', $data);
        return new Response($result);
    }

    /**
     * Get payment request details by UUID
     * 
     * @param string $uuid Payment request UUID
     * @return Response
     * @throws ValidationException if UUID is invalid
     * @throws PaysuiteException if API request fails
     */
    public function getPayment(string $uuid): Response
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
            throw new ValidationException('Invalid UUID format');
        }
        $result = $this->request('GET', "payments/{$uuid}");
        return new Response($result);
    }

    /**
     * Validates payment request data
     * 
     * @param array $data
     * @throws ValidationException
     */
    private function validatePaymentRequestData(array $data): void
    {
        $requiredFields = ['amount', 'reference', 'description', 'return_url'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new ValidationException("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new ValidationException('Amount must be a positive number');
        }

        if (!filter_var($data['return_url'], FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid return URL');
        }
    }

    /**
     * Makes an HTTP request to the API
     * 
     * @param string $method HTTP method
     * @param string $path API endpoint path
     * @param array $data Request data for POST requests
     * @return string Response body
     * @throws PaysuiteException if request fails
     */
    public function request(string $method, string $path = '', array $data = []): string
    {
        $url = $this->api_url . '/' . $path;

        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $this->getToken()
            ],
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new PaysuiteException('Curl Error: ' . $error);
        }

        if ($statusCode >= 400) {
            $responseData = json_decode($response, true);
            $message = $responseData['message'] ?? 'Unknown error occurred';
            throw new PaysuiteException($message, $statusCode);
        }

        if (!$response) {
            throw new PaysuiteException('Failed to get response from server');
        }

        return $response;
    }
}
