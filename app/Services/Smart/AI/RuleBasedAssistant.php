<?php

namespace App\Services\Smart\AI;

use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;
use App\Services\Smart\FarmerInsightsService;
use Illuminate\Support\Facades\Cache;

/**
 * Rule-based AI Assistant implementation.
 * Provides contextual responses without external AI API.
 * Can be extended with external AI later by implementing AssistantInterface.
 */
class RuleBasedAssistant implements AssistantInterface
{
    protected KnowledgeBase $knowledgeBase;
    protected array $intents = [];
    protected array $responses = [];

    public function __construct(?KnowledgeBase $knowledgeBase = null)
    {
        $this->knowledgeBase = $knowledgeBase ?? new KnowledgeBase();

        $this->loadIntents();
        $this->loadResponses();
    }

    public function getProviderName(): string
    {
        return 'Rule-Based Assistant';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function sendMessage(string $message, array $context = []): AssistantResponse
    {
        $this->knowledgeBase->setContext($context);

        $intent = $this->detectIntent($message);
        $entities = $this->extractEntities($message, $intent);

        $response = $this->generateResponse($intent, $entities, $context);

        return AssistantResponse::success(
            $response['message'],
            ['intent' => $intent, 'entities' => $entities],
            $response['suggestions'] ?? []
        );
    }

    public function getSuggestedQuestions(array $context = []): array
    {
        $this->knowledgeBase->setContext($context);
        $role = $context['role'] ?? 'guest';

        $suggestions = [
            'guest' => [
                'How do I create an account?',
                'What products are available?',
                'How does AbodaFarm work?',
                'What payment methods are accepted?',
            ],
            'pelanggan' => [
                'How do I track my order?',
                'How do I make a booking?',
                'What is my order status?',
                'How do I contact the farmer?',
            ],
            'peternak' => [
                'How do I add a new product?',
                'How are my sales performing?',
                'How do I manage my orders?',
                'How do I update my farm profile?',
            ],
            'staf' => [
                'How do I view my deliveries?',
                'How do I update delivery status?',
                'What deliveries are assigned to me?',
            ],
            'admin' => [
                'Show me the analytics dashboard',
                'How many pending verifications?',
                'What are the top products?',
            ],
        ];

        return $suggestions[$role] ?? $suggestions['guest'];
    }

    protected function loadIntents(): void
    {
        $this->intents = [
            // Order related
            'track_order' => [
                'patterns' => ['track', 'lacak', 'where is my order', 'status pesanan', 'delivery status', 'pengiriman'],
                'keywords' => ['order', 'pesanan', 'delivery', 'pengiriman', 'lacak'],
            ],
            'order_status' => [
                'patterns' => ['order status', 'status pesanan', 'pesanan saya', 'my order'],
                'keywords' => ['order', 'pesanan', 'status'],
            ],
            'cancel_order' => [
                'patterns' => ['cancel', 'batal', 'cancel order', 'batalkan pesanan'],
                'keywords' => ['cancel', 'batal', 'hapus'],
            ],

            // Booking related
            'booking_info' => [
                'patterns' => ['booking', 'reservasi', 'how to book', 'cara booking', 'cara pesan'],
                'keywords' => ['booking', 'reservasi', 'pesan', 'schedule'],
            ],

            // Payment related
            'payment_info' => [
                'patterns' => ['payment', 'pembayaran', 'how to pay', 'cara bayar', 'metode pembayaran'],
                'keywords' => ['payment', 'pembayaran', 'bayar', 'transfer', 'midtrans'],
            ],

            // Product related
            'find_product' => [
                'patterns' => ['find', 'cari', 'search', 'looking for', 'mencari', 'butuh'],
                'keywords' => ['product', 'produk', 'ayam', 'sapi', 'kambing', 'pakan'],
            ],
            'product_info' => [
                'patterns' => ['product details', 'detail produk', 'about product', 'tentang produk'],
                'keywords' => ['product', 'produk', 'spec', 'spesifikasi'],
            ],

            // Delivery related
            'delivery_info' => [
                'patterns' => ['delivery', 'pengiriman', 'shipping', 'how long', 'berapa lama'],
                'keywords' => ['delivery', 'pengiriman', 'shipping', 'kirim', 'terima'],
            ],

            // Account related
            'account_help' => [
                'patterns' => ['account', 'akun', 'login', 'register', 'signup', 'daftar', 'masuk'],
                'keywords' => ['account', 'akun', 'login', 'register', 'password', 'sandi'],
            ],

            // Farmer related
            'farmer_help' => [
                'patterns' => ['add product', 'tambah produk', 'manage products', 'manage farm', 'atur farm'],
                'keywords' => ['farmer', 'peternak', 'product', 'produk', 'stock', 'stok', 'jual'],
            ],

            // Staff related
            'staff_delivery' => [
                'patterns' => ['delivery task', 'delivery assigned', 'my deliveries', 'pengiriman saya'],
                'keywords' => ['delivery', 'pengiriman', 'task', 'tugas', 'assign', 'tugaskan'],
            ],

            // General
            'greeting' => [
                'patterns' => ['hi', 'hello', 'hey', 'halo', 'hai', 'assalamualaikum'],
                'keywords' => ['hi', 'hello', 'hey', 'halo', 'hai'],
            ],
            'thanks' => [
                'patterns' => ['thanks', 'thank you', 'terima kasih', 'makasih', 'thx'],
                'keywords' => ['thanks', 'thank', 'terima kasih'],
            ],
            'help' => [
                'patterns' => ['help', 'bantuan', 'how', 'cara', 'what', 'apa'],
                'keywords' => ['help', 'bantuan', 'how', 'cara', 'what', 'apa', '?', 'tolong'],
            ],
        ];
    }

    protected function loadResponses(): void
    {
        $this->responses = [
            'track_order' => [
                'message' => "To track your order:\n\n1. Go to \"My Orders\" in your account\n2. Select the order you want to track\n3. Click \"Track Delivery\" to see real-time status\n\nYou can also use the delivery code sent to your email/SMS to check the status directly.",
                'suggestions' => [
                    'How do I contact the courier?',
                    'What if my delivery is late?',
                    'How do I report a delivery issue?',
                ],
            ],
            'order_status' => [
                'message' => "You can check your order status in \"My Orders\" section. Orders go through these statuses:\n\n• Pending - Waiting for payment confirmation\n• Processing - Order is being prepared\n• Shipped - On the way to you\n• Delivered - Received at destination\n• Completed - Order finished\n• Cancelled - Order was cancelled",
                'suggestions' => [
                    'How do I track my order?',
                    'How do I cancel an order?',
                    'What if I have a problem with my order?',
                ],
            ],
            'booking_info' => [
                'message' => "Here's how to make a booking:\n\n1. Browse products and find what you need\n2. Click \"Book Now\" on the product page\n3. Select date, quantity, and provide details\n4. Complete payment\n5. The farmer will confirm your booking\n\nYou'll receive notifications about booking status updates.",
                'suggestions' => [
                    'Can I modify my booking?',
                    'What is the cancellation policy?',
                    'How do I pay for a booking?',
                ],
            ],
            'payment_info' => [
                'message' => "AbodaFarm accepts the following payment methods:\n\n• Bank Transfer (BCA, BNI, BRI, Mandiri)\n• E-Wallet (GoPay, OVO, Dana, LinkAja)\n• Credit/Debit Card (Visa, Mastercard)\n• Midtrans Payment Gateway\n\nAll payments are securely processed. You can view payment instructions after placing your order.",
                'suggestions' => [
                    'When will I be charged?',
                    'How do I confirm payment?',
                    'How do I get a refund?',
                ],
            ],
            'find_product' => [
                'message' => "You can find products by:\n\n• Using the search bar at the top\n• Browsing categories\n• Filtering by type (Livestock, Fresh Meat, Supplies)\n• Checking location-based recommendations\n\nUse keywords like \"ayam\", \"sapi\", \"pakan\" to find specific products.",
                'suggestions' => [
                    'Show me chicken products',
                    'Show me cattle for sale',
                    'Show me livestock feed',
                ],
            ],
            'delivery_info' => [
                'message' => "Delivery times depend on:\n\n• Distance from the farm\n• Product availability\n• Delivery method selected\n\nTypical delivery:\n• Local: 1-2 days\n• Regional: 2-4 days\n• Long distance: 4-7 days\n\nYou'll receive tracking updates via SMS and email.",
                'suggestions' => [
                    'Can I change my delivery address?',
                    'What if delivery fails?',
                    'Do you deliver on weekends?',
                ],
            ],
            'account_help' => [
                'message' => "To get started with AbodaFarm:\n\n**For Customers:**\n1. Click \"Register\" and select \"Customer\"\n2. Fill in your details\n3. Verify your email\n4. Start shopping!\n\n**For Farmers:**\n1. Register and select \"Farmer\"\n2. Complete your farm profile\n3. Submit for verification\n4. Once approved, start listing products",
                'suggestions' => [
                    'How do I reset my password?',
                    'How do I verify my account?',
                    'Is my data secure?',
                ],
            ],
            'farmer_help' => [
                'message' => "As a farmer, you can:\n\n**Product Management:**\n1. Go to \"Livestock\" section\n2. Click \"Add New\"\n3. Fill product details and set pricing\n4. Manage your stock\n\n**Sales:**\n• View orders in \"Orders\" section\n• Update order status\n• Track your sales in \"Analytics\"\n\n**Profile:**\n• Keep your farm profile updated\n• Add photos to build trust\n• Respond to customer reviews",
                'suggestions' => [
                    'How do I update stock?',
                    'How do I view my sales?',
                    'How do I get more customers?',
                ],
            ],
            'staff_delivery' => [
                'message' => "To manage your deliveries:\n\n1. Go to \"Shipping\" in your dashboard\n2. View assigned deliveries\n3. Accept delivery tasks\n4. Update status as you deliver\n5. Mark as delivered with proof\n\nUse the mobile-friendly interface to update statuses on the go.",
                'suggestions' => [
                    'How do I mark delivery as failed?',
                    'How do I add proof of delivery?',
                    'What if I have trouble with navigation?',
                ],
            ],
            'greeting' => [
                'message' => "Halo! 👋 Welcome to AbodaFarm!\n\nI'm your virtual assistant. I can help you with:\n• Order tracking and status\n• Booking information\n• Payment guidance\n• Product search\n• Account help\n\nWhat can I help you with today?",
                'suggestions' => [
                    'How do I track my order?',
                    'Show me available products',
                    'How do I make a booking?',
                ],
            ],
            'thanks' => [
                'message' => "You're welcome! 😊 Is there anything else I can help you with?\n\nFeel free to ask if you have any other questions about AbodaFarm.",
                'suggestions' => [
                    'Show me available products',
                    'How do I track my order?',
                    'Contact customer support',
                ],
            ],
            'help' => [
                'message' => "I can help you with many things on AbodaFarm:\n\n• **Orders & Bookings** - Track orders, make bookings\n• **Payments** - Payment methods and confirmation\n• **Products** - Finding and purchasing products\n• **Delivery** - Tracking and delivery information\n• **Account** - Registration, login, profile\n• **Farmers** - Product management, sales\n\nJust type your question or select from the suggestions below!",
                'suggestions' => [
                    'How do I track my order?',
                    'What payment methods are accepted?',
                    'How do I create an account?',
                ],
            ],
        ];
    }

    protected function detectIntent(string $message): string
    {
        $messageLower = mb_strtolower($message);

        foreach ($this->intents as $intent => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (str_contains($messageLower, mb_strtolower($pattern))) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

    protected function extractEntities(string $message, string $intent): array
    {
        $entities = [];
        $messageLower = mb_strtolower($message);

        // Extract order numbers
        if (preg_match('/(?:order|pesanan|#)(?:\s*)?(\d+)/i', $message, $matches)) {
            $entities['order_number'] = $matches[1];
        }

        // Extract product names
        $products = ['ayam', 'sapi', 'kambing', 'bebek', 'pakan', 'obat', 'vitamin', 'telur'];
        foreach ($products as $product) {
            if (str_contains($messageLower, $product)) {
                $entities['product_type'] = $product;
                break;
            }
        }

        // Extract delivery codes
        if (preg_match('/(?:delivery|kode|code)(?:\s*)?[:\-]?\s*([A-Z0-9]{6,})/i', $message, $matches)) {
            $entities['delivery_code'] = $matches[1];
        }

        return $entities;
    }

    protected function generateResponse(string $intent, array $entities, array $context): array
    {
        $role = $context['role'] ?? 'guest';
        $userId = $context['user_id'] ?? null;

        // Handle personalized responses
        if ($intent === 'order_status' && $userId && isset($entities['order_number'])) {
            $response = $this->getPersonalizedOrderStatus($entities['order_number'], $userId);
            if ($response) {
                return $response;
            }
        }

        if ($intent === 'track_order' && $userId) {
            $response = $this->getRecentOrders($userId);
            if ($response) {
                return $response;
            }
        }

        // Check for FAQ matches
        $faqResponse = $this->searchFaq($entities['product_type'] ?? '', $role);
        if ($faqResponse) {
            return $faqResponse;
        }

        // Return configured response
        if (isset($this->responses[$intent])) {
            return $this->responses[$intent];
        }

        // Default response
        return [
            'message' => "I'm not sure I understand that question. Let me help you find what you need:\n\n• Type your question more specifically\n• Use keywords like \"order\", \"payment\", \"delivery\"\n• Or select from the suggested questions below\n\nIf you need more help, you can also contact our customer support team.",
            'suggestions' => $this->getSuggestedQuestions($context),
        ];
    }

    protected function getPersonalizedOrderStatus(string $orderNumber, int $userId): ?array
    {
        $order = Order::where('order_number', 'like', "%{$orderNumber}%")
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return null;
        }

        $timeline = $order->statusLogs()->orderByDesc('created_at')->limit(3)->get();

        $message = "Here is your order #{$order->order_number} status:\n\n";
        $message .= "**Status:** {$order->status_label}\n";
        $message .= "**Total:** {$order->formatted_total}\n";
        $message .= "**Date:** {$order->created_at->format('d M Y H:i')}\n\n";

        if ($timeline->isNotEmpty()) {
            $message .= "Recent Updates:\n";
            foreach ($timeline as $log) {
                $message .= "• {$log->status} - {$log->created_at->diffForHumans()}\n";
            }
        }

        return [
            'message' => $message,
            'suggestions' => [
                'Track another order',
                'View all my orders',
                'Contact support',
            ],
        ];
    }

    protected function getRecentOrders(int $userId): ?array
    {
        $recentOrders = Order::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        if ($recentOrders->isEmpty()) {
            return null;
        }

        $message = "Here are your recent orders:\n\n";
        foreach ($recentOrders as $order) {
            $message .= "**#{$order->order_number}** - {$order->status_label}\n";
            $message .= "   {$order->formatted_total} | {$order->created_at->diffForHumans()}\n\n";
        }

        $message .= "You can track any order from your dashboard or type your order number to get details.";

        return [
            'message' => $message,
            'suggestions' => [
                'Track specific order',
                'View all orders',
                'Make a new order',
            ],
        ];
    }

    protected function searchFaq(string $query, string $role): ?array
    {
        if (empty($query)) {
            return null;
        }

        $faq = Faq::where(function ($q) use ($query) {
            $q->whereRaw('LOWER(question) LIKE ?', ['%' . mb_strtolower($query) . '%'])
                ->orWhereRaw('LOWER(answer) LIKE ?', ['%' . mb_strtolower($query) . '%']);
        })
            ->where(function ($q) use ($role) {
                $q->whereNull('role')
                    ->orWhereJsonContains('roles', $role)
                    ->orWhere('roles', 'all');
            })
            ->first();

        if (!$faq) {
            return null;
        }

        return [
            'message' => "Based on your question, here is a relevant FAQ:\n\n**Q: {$faq->question}**\n\n{$faq->answer}",
            'suggestions' => [
                'Show more FAQs',
                'Ask another question',
                'Contact support',
            ],
        ];
    }
}
