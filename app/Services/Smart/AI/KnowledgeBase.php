<?php

namespace App\Services\Smart\AI;

use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;

class KnowledgeBase
{
    protected array $context = [];

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getUserRole(): string
    {
        return $this->context['role'] ?? 'guest';
    }

    public function getUserId(): ?int
    {
        return $this->context['user_id'] ?? null;
    }

    /**
     * Get relevant FAQs based on the query and user role.
     */
    public function getRelevantFaqs(string $query, int $limit = 5): array
    {
        $role = $this->getUserRole();

        return Faq::query()
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(question) LIKE ?', ['%' . mb_strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(answer) LIKE ?', ['%' . mb_strtolower($query) . '%']);
            })
            ->where(function ($q) use ($role) {
                $q->whereNull('role')
                    ->orWhereJsonContains('roles', $role)
                    ->orWhere('roles', 'all');
            })
            ->limit($limit)
            ->get(['id', 'question', 'answer', 'category'])
            ->toArray();
    }

    /**
     * Get all FAQs grouped by category.
     */
    public function getFaqsByCategory(): array
    {
        $role = $this->getUserRole();

        return Faq::where(function ($q) use ($role) {
            $q->whereNull('role')
                ->orWhereJsonContains('roles', $role)
                ->orWhere('roles', 'all');
        })
            ->orderBy('category')
            ->orderBy('question')
            ->get()
            ->groupBy('category')
            ->map(fn($faqs) => $faqs->toArray())
            ->toArray();
    }

    /**
     * Get user-specific data based on role.
     */
    public function getUserData(): array
    {
        $role = $this->getUserRole();
        $userId = $this->getUserId();

        if (!$userId) {
            return [];
        }

        return match ($role) {
            'pelanggan' => $this->getCustomerData($userId),
            'peternak' => $this->getFarmerData($userId),
            'staf' => $this->getStaffData($userId),
            'admin' => $this->getAdminData($userId),
            default => [],
        };
    }

    protected function getCustomerData(int $userId): array
    {
        $orders = Order::where('user_id', $userId)
            ->with('items.product')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendingOrders = $orders->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])->count();
        $completedOrders = $orders->where('status', Order::STATUS_COMPLETED)->count();

        return [
            'total_orders' => Order::where('user_id', $userId)->count(),
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'recent_orders' => $orders->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'status_label' => $o->status_label,
                'total' => $o->formatted_total,
                'created_at' => $o->created_at->diffForHumans(),
            ]),
        ];
    }

    protected function getFarmerData(int $userId): array
    {
        $orders = Order::whereHas('items', fn($q) => $q->where('farmer_id', $userId))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'total_products' => Product::where('user_id', $userId)->count(),
            'active_products' => Product::where('user_id', $userId)->available()->count(),
            'pending_orders' => $orders->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])->count(),
        ];
    }

    protected function getStaffData(int $userId): array
    {
        return [
            'role' => 'staff',
            'permissions' => [
                'Can view assigned deliveries',
                'Can update delivery status',
                'Can view order details',
            ],
        ];
    }

    protected function getAdminData(int $userId): array
    {
        return [
            'role' => 'admin',
            'permissions' => [
                'Full system access',
                'Can manage products',
                'Can manage users',
                'Can view analytics',
            ],
        ];
    }

    /**
     * Search products based on query.
     */
    public function searchProducts(string $query, int $limit = 5): array
    {
        return Product::whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($query) . '%'])
            ->available()
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'price', 'image'])
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->formatted_current_price,
                'image' => $p->image_url,
            ])
            ->toArray();
    }

    /**
     * Get help topics based on user role.
     */
    public function getHelpTopics(): array
    {
        $role = $this->getUserRole();

        $commonTopics = [
            [
                'id' => 'getting-started',
                'title' => 'Getting Started',
                'icon' => 'rocket',
                'questions' => [
                    'How do I create an account?',
                    'How do I verify my account?',
                    'What payment methods are accepted?',
                ],
            ],
            [
                'id' => 'orders',
                'title' => 'Orders & Bookings',
                'icon' => 'shopping-bag',
                'questions' => [
                    'How do I track my order?',
                    'How do I cancel an order?',
                    'What is the booking process?',
                ],
            ],
            [
                'id' => 'payments',
                'title' => 'Payments',
                'icon' => 'credit-card',
                'questions' => [
                    'What payment methods are accepted?',
                    'When will I be charged?',
                    'How do I get a refund?',
                ],
            ],
            [
                'id' => 'delivery',
                'title' => 'Delivery',
                'icon' => 'truck',
                'questions' => [
                    'How long does delivery take?',
                    'Can I change my delivery address?',
                    'What if my delivery fails?',
                ],
            ],
        ];

        $roleTopics = match ($role) {
            'pelanggan' => [
                [
                    'id' => 'account',
                    'title' => 'My Account',
                    'icon' => 'user',
                    'questions' => [
                        'How do I update my profile?',
                        'How do I change my password?',
                        'How do I manage my addresses?',
                    ],
                ],
                [
                    'id' => 'favorites',
                    'title' => 'Favorites & Wishlist',
                    'icon' => 'heart',
                    'questions' => [
                        'How do I save products?',
                        'How do I follow farmers?',
                    ],
                ],
            ],
            'peternak' => [
                [
                    'id' => 'products',
                    'title' => 'Managing Products',
                    'icon' => 'box',
                    'questions' => [
                        'How do I add a new product?',
                        'How do I update stock?',
                        'How do I set product pricing?',
                    ],
                ],
                [
                    'id' => 'sales',
                    'title' => 'Sales & Analytics',
                    'icon' => 'chart-line',
                    'questions' => [
                        'How do I view my sales?',
                        'How do I view my analytics?',
                        'When will I receive payments?',
                    ],
                ],
            ],
            'staf' => [
                [
                    'id' => 'deliveries',
                    'title' => 'Delivery Management',
                    'icon' => 'truck',
                    'questions' => [
                        'How do I accept a delivery?',
                        'How do I update delivery status?',
                        'How do I mark delivery as failed?',
                    ],
                ],
            ],
            'admin' => [
                [
                    'id' => 'management',
                    'title' => 'System Management',
                    'icon' => 'cog',
                    'questions' => [
                        'How do I manage users?',
                        'How do I verify farmers?',
                        'How do I create promotions?',
                    ],
                ],
            ],
            default => [],
        };

        return array_merge($commonTopics, $roleTopics);
    }
}
