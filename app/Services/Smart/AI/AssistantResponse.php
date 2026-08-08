<?php

namespace App\Services\Smart\AI;

class AssistantResponse
{
    public function __construct(
        public readonly string $message,
        public readonly bool $success = true,
        public readonly ?string $error = null,
        public readonly array $metadata = [],
        public readonly array $suggestions = [],
        public readonly ?string $provider = null
    ) {}

    public static function success(string $message, array $metadata = [], array $suggestions = []): self
    {
        return new self(
            message: $message,
            success: true,
            metadata: $metadata,
            suggestions: $suggestions
        );
    }

    public static function error(string $error): self
    {
        return new self(
            message: '',
            success: false,
            error: $error
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'success' => $this->success,
            'error' => $this->error,
            'metadata' => $this->metadata,
            'suggestions' => $this->suggestions,
            'provider' => $this->provider,
        ];
    }
}
