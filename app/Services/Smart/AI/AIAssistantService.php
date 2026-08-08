<?php

namespace App\Services\Smart\AI;

/**
 * AI Assistant Service
 * Manages AI assistant providers and provides a unified interface.
 *
 * Architecture:
 * - Default implementation: RuleBasedAssistant (no external AI)
 * - Extensible: Add new providers by implementing AssistantInterface
 *
 * To add external AI (e.g., OpenAI, Claude):
 * 1. Create a new provider class implementing AssistantInterface
 * 2. Register it in the providers() method
 * 3. Set the active provider via config or method
 */
class AIAssistantService
{
    protected AssistantInterface $assistant;
    protected KnowledgeBase $knowledgeBase;
    protected string $activeProvider = 'rule-based';

    public function __construct(?AssistantInterface $assistant = null)
    {
        $this->knowledgeBase = new KnowledgeBase();
        $this->assistant = $assistant ?? new RuleBasedAssistant($this->knowledgeBase);
    }

    /**
     * Set the active provider.
     */
    public function setProvider(string $provider): self
    {
        $this->activeProvider = $provider;
        $this->assistant = $this->createProvider($provider);
        return $this;
    }

    /**
     * Get the active provider name.
     */
    public function getActiveProvider(): string
    {
        return $this->assistant->getProviderName();
    }

    /**
     * Check if the current provider is available.
     */
    public function isAvailable(): bool
    {
        return $this->assistant->isAvailable();
    }

    /**
     * Send a message and get a response.
     */
    public function ask(string $message, array $context = []): AssistantResponse
    {
        // Validate context for security
        $context = $this->sanitizeContext($context);

        return $this->assistant->sendMessage($message, $context);
    }

    /**
     * Get suggested questions based on user context.
     */
    public function getSuggestions(array $context = []): array
    {
        $context = $this->sanitizeContext($context);
        return $this->assistant->getSuggestedQuestions($context);
    }

    /**
     * Get help topics based on user role.
     */
    public function getHelpTopics(array $context = []): array
    {
        $context = $this->sanitizeContext($context);
        return $this->knowledgeBase->setContext($context)->getHelpTopics();
    }

    /**
     * Get FAQs for the user.
     */
    public function getFaqs(array $context = []): array
    {
        $context = $this->sanitizeContext($context);
        return $this->knowledgeBase->setContext($context)->getFaqsByCategory();
    }

    /**
     * Search FAQs.
     */
    public function searchFaqs(string $query, array $context = []): array
    {
        $context = $this->sanitizeContext($context);
        return $this->knowledgeBase->setContext($context)->getRelevantFaqs($query);
    }

    /**
     * Get available providers.
     */
    public function getProviders(): array
    {
        return [
            'rule-based' => [
                'name' => 'Rule-Based Assistant',
                'description' => 'Contextual responses based on rules and FAQs. No external AI required.',
                'available' => true,
                'requires_api_key' => false,
            ],
        ];
    }

    /**
     * Create a provider instance.
     */
    protected function createProvider(string $provider): AssistantInterface
    {
        return match ($provider) {
            'rule-based' => new RuleBasedAssistant($this->knowledgeBase),
            // Add more providers here when needed:
            // 'openai' => new OpenAIAssistant($this->knowledgeBase),
            // 'claude' => new ClaudeAssistant($this->knowledgeBase),
            default => new RuleBasedAssistant($this->knowledgeBase),
        };
    }

    /**
     * Sanitize context for security.
     * Ensures users can only access their own data.
     */
    protected function sanitizeContext(array $context): array
    {
        // Only allow specific context keys
        $allowedKeys = ['role', 'user_id', 'session_id'];

        $sanitized = array_intersect_key($context, array_flip($allowedKeys));

        // Validate role
        $validRoles = ['guest', 'pelanggan', 'peternak', 'staf', 'admin'];
        if (isset($sanitized['role']) && !in_array($sanitized['role'], $validRoles)) {
            unset($sanitized['role']);
        }

        // Validate user_id is numeric
        if (isset($sanitized['user_id']) && !is_numeric($sanitized['user_id'])) {
            unset($sanitized['user_id']);
        }

        return $sanitized;
    }

    /**
     * Get user-specific data (secure).
     */
    public function getUserData(int $userId, string $role): array
    {
        // Only return data for the requesting user
        return $this->knowledgeBase->setContext([
            'user_id' => $userId,
            'role' => $role,
        ])->getUserData();
    }

    /**
     * Build context from user request.
     */
    public function buildContext(?\App\Models\User $user = null): array
    {
        if (!$user) {
            return ['role' => 'guest'];
        }

        return [
            'role' => $user->getRoleNames()->first() ?? 'guest',
            'user_id' => $user->id,
        ];
    }
}
