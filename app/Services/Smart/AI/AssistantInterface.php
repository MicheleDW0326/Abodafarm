<?php

namespace App\Services\Smart\AI;

/**
 * Interface for AI Assistant providers.
 * Implement this interface to add new AI providers.
 */
interface AssistantInterface
{
    /**
     * Get the provider name.
     */
    public function getProviderName(): string;

    /**
     * Check if the provider is available/configured.
     */
    public function isAvailable(): bool;

    /**
     * Send a message and get a response.
     *
     * @param string $message User message
     * @param array $context Context data (user role, user id, etc.)
     * @return AssistantResponse
     */
    public function sendMessage(string $message, array $context = []): AssistantResponse;

    /**
     * Get suggested questions based on context.
     *
     * @param array $context Context data
     * @return array
     */
    public function getSuggestedQuestions(array $context = []): array;
}
