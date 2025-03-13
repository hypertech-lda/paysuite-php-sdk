<?php

namespace Hypertech\Paysuite\Message;

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
}