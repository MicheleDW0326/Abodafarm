<?php

namespace App\Livewire\Smart;

use App\Services\Smart\AI\AIAssistantService;
use App\Services\Smart\AI\AssistantResponse;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class AIAssistant extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $conversations = [];
    public array $suggestions = [];
    public array $helpTopics = [];
    public array $faqs = [];
    public bool $isLoading = false;
    public string $activeTab = 'chat';
    public ?AssistantResponse $lastResponse = null;

    protected AIAssistantService $assistantService;

    public function boot(AIAssistantService $assistantService): void
    {
        $this->assistantService = $assistantService;
    }

    public function mount(): void
    {
        $context = $this->buildContext();
        $this->suggestions = $this->assistantService->getSuggestions($context);
        $this->helpTopics = $this->assistantService->getHelpTopics($context);
        $this->faqs = $this->assistantService->getFaqs($context);

        // Add initial greeting
        $this->conversations[] = [
            'type' => 'assistant',
            'message' => "Halo! 👋 Saya asisten virtual AbodaFarm. Saya bisa membantu Anda dengan:\n\n• Lacak pesanan dan status pengiriman\n• Informasi booking dan pemesanan\n• Panduan pembayaran\n• Pencarian produk\n• Bantuan akun\n\nApa yang bisa saya bantu hari ini?",
            'suggestions' => $this->suggestions,
        ];
    }

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = trim($this->message);
        $this->message = '';

        // Add user message to conversation
        $this->conversations[] = [
            'type' => 'user',
            'message' => $userMessage,
        ];

        $this->isLoading = true;

        // Get response
        $context = $this->buildContext();
        $response = $this->assistantService->ask($userMessage, $context);

        $this->lastResponse = $response;

        // Add assistant response to conversation
        $this->conversations[] = [
            'type' => 'assistant',
            'message' => $response->message,
            'suggestions' => $response->suggestions,
            'success' => $response->success,
        ];

        $this->isLoading = false;
    }

    public function sendSuggestion(string $suggestion): void
    {
        $this->message = $suggestion;
        $this->sendMessage();
    }

    public function selectHelpTopic(string $topicId): void
    {
        $topic = collect($this->helpTopics)->firstWhere('id', $topicId);
        if ($topic && !empty($topic['questions'])) {
            $this->message = $topic['questions'][0];
            $this->sendMessage();
        }
    }

    public function selectFaq(int $faqId): void
    {
        $faqs = $this->assistantService->searchFaqs('', $this->buildContext());
        $faq = collect($faqs)->firstWhere('id', $faqId);
        if ($faq) {
            $this->message = $faq['question'];
            $this->sendMessage();
        }
    }

    public function clearConversation(): void
    {
        $this->conversations = [];
        $this->mount();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    protected function buildContext(): array
    {
        $user = auth()->user();

        if (!$user) {
            return ['role' => 'guest'];
        }

        return [
            'role' => $user->getRoleNames()->first() ?? 'guest',
            'user_id' => $user->id,
        ];
    }

    public function render()
    {
        return view('livewire.smart.ai-assistant', [
            'provider' => $this->assistantService->getActiveProvider(),
        ]);
    }
}
