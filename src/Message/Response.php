<?php

namespace Hypertech\Paysuite\Message;

    /**
     * Represents a response from the PaySuite API
     *
     * @property string $status Response status (success or error)
     * @property array $data Response data (contains reference, checkout_url and amount)
     * @property string|null $message Error message if request was not successful
     * @property array $content Raw response content
     */
/**
 * Represents a response from the PaySuite API
 *
 * @property string $status Response status (success or error)
 * @property array $data Response data (contains reference, checkout_url and amount)
 * @property string|null $message Error message if request was not successful
 * @property array $content Raw response content
 */
/**
 * Represents a response from the PaySuite API
 *
 * This class is responsible for handling the API response content, extracting
 * status, data, error message, and other relevant information from it.
 * It provides methods to access these properties and to check if the request
 * was successful.
 *
 * @property string $status   Response status (e.g., 'success' or 'error')
 * @property array $data      Response data containing details like reference,
 *                            checkout_url, and amount
 * @property string|null $message Error message if the request was not successful
 * @property array $content   Raw response content as an associative array
 */
class Response
{
    private string $status;
    private array $data;
    private ?string $message;
    private array $content;

    public function __construct(string $content)
    {
        $this->content = json_decode($content, true);
        $this->initialize();
    }

    /**
     * Initialize response properties from API response
     */
    private function initialize(): void
    {
        $this->status = $this->content['status'] ?? '';
        $this->data = $this->content['data'] ?? [];
        $this->message = $this->content['message'] ?? null;
    }

    /**
     * Check if the request was successful
     */
    public function isSuccessfully(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Get the response data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get error message if present
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get the raw response content
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Get the payment reference
     *
     * @return string|null Payment reference, or null if not present
     */
    public function getReference(): ?string
    {
        return $this->data['reference'] ?? null;
    }

    /**
     * Get the checkout URL to be used to redirect the user to complete the payment
     *
     * @return string|null URL to redirect the user to complete the payment, or null if not present
     */
    public function getCheckoutUrl(): ?string
    {
        return $this->data['checkout_url'] ?? null;
    }


    /**
     * Get the payment amount
     *
     * @return string|null Payment amount, or null if not present
     */
    public function getAmount(): ?string
    {
        return $this->data['amount'] ?? null;
    }

}