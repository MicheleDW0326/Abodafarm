<div>
    <!-- Floating Chat Button -->
    @if(!$isOpen)
        <button
            wire:click="toggleChat"
            class="fixed bottom-6 right-6 z-50 w-16 h-16 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-105"
            title="Chat dengan kami"
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full animate-pulse"></span>
        </button>
    @endif

    <!-- Chat Window -->
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-end justify-end p-4 sm:p-6" wire:click.self="toggleChat">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md h-[600px] max-h-[80vh] flex flex-col overflow-hidden animate-slide-up"
                x-data="{ show: true }"
                x-show="show"
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">AbodaFarm Assistant</h3>
                            <p class="text-emerald-100 text-xs">{{ $provider }}</p>
                        </div>
                    </div>
                    <button
                        wire:click="toggleChat"
                        class="p-2 hover:bg-white/20 rounded-full transition"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button
                        wire:click="setTab('chat')"
                        class="flex-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'chat' ? 'text-emerald-600 border-b-2 border-emerald-600' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                    >
                        Chat
                    </button>
                    <button
                        wire:click="setTab('help')"
                        class="flex-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'help' ? 'text-emerald-600 border-b-2 border-emerald-600' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                    >
                        Help Topics
                    </button>
                    <button
                        wire:click="setTab('faq')"
                        class="flex-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'faq' ? 'text-emerald-600 border-b-2 border-emerald-600' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                    >
                        FAQs
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto">
                    @if($activeTab === 'chat')
                        <!-- Conversations -->
                        <div class="p-4 space-y-4">
                            @foreach($conversations as $index => $conversation)
                                @if($conversation['type'] === 'user')
                                    <div class="flex justify-end">
                                        <div class="bg-emerald-600 text-white rounded-2xl rounded-tr-sm px-4 py-2 max-w-[80%]">
                                            <p class="text-sm whitespace-pre-wrap">{!! nl2br(e($conversation['message'])) !!}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex justify-start">
                                        <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-tl-sm px-4 py-2 max-w-[80%]">
                                            <div class="flex items-start gap-2">
                                                <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{!! nl2br(e($conversation['message'])) !!}</p>

                                                    @if(!empty($conversation['suggestions']))
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach($conversation['suggestions'] as $suggestion)
                                                                <button
                                                                    wire:click="sendSuggestion('{{ e($suggestion) }}')"
                                                                    class="text-xs px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full border border-emerald-200 dark:border-emerald-700 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition"
                                                                >
                                                                    {{ $suggestion }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if($isLoading)
                                <div class="flex justify-start">
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @elseif($activeTab === 'help')
                        <!-- Help Topics -->
                        <div class="p-4 space-y-3">
                            @foreach($helpTopics as $topic)
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                                    <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-2 mb-2">
                                        @switch($topic['icon'])
                                            @case('rocket')
                                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.05 5L5.05 15l1.41 1.41L11 11.4V19h2v-7.6l5.54 5.54 1.41-1.41L15.05 5z"/>
                                                </svg>
                                                @break
                                            @case('shopping-bag')
                                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                @break
                                            @case('credit-card')
                                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                                @break
                                            @case('truck')
                                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                                </svg>
                                                @break
                                            @default
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                        @endswitch
                                        {{ $topic['title'] }}
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($topic['questions'] as $question)
                                            <button
                                                wire:click="sendSuggestion('{{ e($question) }}')"
                                                class="block w-full text-left text-sm text-gray-600 dark:text-gray-300 hover:text-emerald-600 dark:hover:text-emerald-400 py-1 transition"
                                            >
                                                {{ $question }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($activeTab === 'faq')
                        <!-- FAQs -->
                        <div class="p-4 space-y-3">
                            @forelse($faqs as $category => $categoryFaqs)
                                <div class="mb-4">
                                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                        {{ $category }}
                                    </h4>
                                    @foreach($categoryFaqs as $faq)
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 mb-2">
                                            <p class="font-medium text-sm text-gray-900 dark:text-white mb-1">{{ $faq['question'] }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ Str::limit($faq['answer'], 150) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                    <p>No FAQs available yet.</p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>

                <!-- Input -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            wire:model="message"
                            wire:keydown.enter="sendMessage"
                            placeholder="Ketik pesan Anda..."
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                        <button
                            wire:click="sendMessage"
                            wire:disabled="$isLoading || empty(trim($message))"
                            class="p-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white rounded-full transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                        <button
                            wire:click="clearConversation"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                            title="Clear conversation"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    @keyframes slide-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
</style>
@endpush
