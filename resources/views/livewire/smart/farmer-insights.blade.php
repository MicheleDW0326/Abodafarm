<div>
    @if($isLoading)
        <div class="animate-pulse space-y-4">
            <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            </div>
        </div>
    @else
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Business Insights</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">AI-powered recommendations for your farm</p>
                </div>
                <button
                    wire:click="refresh"
                    class="p-2 text-gray-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition"
                    title="Refresh insights"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

            <!-- Best Selling Product -->
            @if(!empty($insights['best_selling']))
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl p-6 text-white">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-emerald-100 text-sm font-medium">Best Seller This Month</p>
                                <h3 class="text-2xl font-bold">{{ $insights['best_selling']['product']['name'] }}</h3>
                                <p class="text-emerald-100">{{ $insights['best_selling']['total_sold'] }} units sold</p>
                            </div>
                        </div>
                        @if($insights['best_selling']['trend'] !== 'stable')
                            <div class="flex items-center gap-1 px-3 py-1 bg-white/20 rounded-full text-sm">
                                @if($insights['best_selling']['trend'] === 'up')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                    </svg>
                                    <span>+{{ $insights['best_selling']['change_percentage'] }}%</span>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                    <span>{{ $insights['best_selling']['change_percentage'] }}%</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <p class="mt-4 text-emerald-100 text-sm">{{ $insights['best_selling']['message'] }}</p>
                </div>
            @endif

            <!-- Sales Trend & Growth -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Sales Trend -->
                @if(!empty($insights['sales_trend']))
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Sales Trend</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Revenue</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($insights['sales_trend']['revenue']['current'] / 1000000, 1) }}M
                                    </span>
                                    @if($insights['sales_trend']['revenue']['trend'] !== 'stable')
                                        <span class="flex items-center text-sm {{ $insights['sales_trend']['revenue']['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600' }}">
                                            @if($insights['sales_trend']['revenue']['trend'] === 'up')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                                +{{ $insights['sales_trend']['revenue']['change'] }}%
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                </svg>
                                                {{ $insights['sales_trend']['revenue']['change'] }}%
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Orders</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $insights['sales_trend']['orders']['current'] }}
                                    </span>
                                    @if($insights['sales_trend']['orders']['trend'] !== 'stable')
                                        <span class="flex items-center text-sm {{ $insights['sales_trend']['orders']['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600' }}">
                                            @if($insights['sales_trend']['orders']['trend'] === 'up')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                                +{{ $insights['sales_trend']['orders']['change'] }}%
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                </svg>
                                                {{ $insights['sales_trend']['orders']['change'] }}%
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ $insights['sales_trend']['message'] }}</p>
                    </div>
                @endif

                <!-- Growth Metrics -->
                @if(!empty($insights['growth_metrics']))
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Growth Metrics</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <p class="text-2xl font-bold text-emerald-600">+{{ $insights['growth_metrics']['monthly_growth'] }}%</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Monthly Growth</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-600">{{ $insights['growth_metrics']['new_customers'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">New Customers</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <p class="text-2xl font-bold text-purple-600">{{ $insights['growth_metrics']['repeat_customers'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Repeat Customers</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-600">+{{ $insights['growth_metrics']['yearly_growth'] }}%</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Yearly Growth</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Low Stock Alerts -->
            @if(!empty($insights['low_stock_alerts']))
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Stock Alerts</h3>
                        <span class="ml-auto text-sm text-gray-500">{{ count($insights['low_stock_alerts']) }} items</span>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($insights['low_stock_alerts'] as $alert)
                            <div class="p-4 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                    @if(!empty($alert['product']['image']))
                                        <img src="{{ $alert['product']['image'] }}" alt="{{ $alert['product']['name'] }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $alert['product']['name'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $alert['message'] }}</p>
                                </div>
                                @if($alert['type'] === 'low_stock')
                                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $alert['urgency'] === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        {{ $alert['current_stock'] ?? 0 }} left
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                        Out of Stock
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recommended Actions -->
            @if(!empty($insights['recommended_actions']))
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Recommended Actions</h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($insights['recommended_actions'] as $action)
                            <div class="p-4 flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                                    @if($action['priority'] === 'high') bg-red-100 dark:bg-red-900/30
                                    @elseif($action['priority'] === 'medium') bg-amber-100 dark:bg-amber-900/30
                                    @else bg-gray-100 dark:bg-gray-700 @endif">
                                    @if($action['type'] === 'restock')
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    @elseif($action['type'] === 'improve_listing')
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $action['title'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $action['description'] }}</p>
                                </div>
                                @if($action['action_url'])
                                    <a href="{{ $action['action_url'] }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                        Take Action →
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Customer Feedback -->
            @if(!empty($insights['customer_feedback']))
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Recent Reviews</h3>
                            <div class="flex items-center gap-1">
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $insights['customer_feedback']['average_rating'] ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-500 ml-1">{{ $insights['customer_feedback']['average_rating'] }} ({{ $insights['customer_feedback']['total_reviews'] }} reviews)</span>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($insights['customer_feedback']['recent_reviews'] as $review)
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                            {{ substr($review['user']['name'], 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $review['user']['name'] }}</span>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= $review['rating'] ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                        </div>
                                        @if($review['comment'])
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ Str::limit($review['comment'], 150) }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-1">{{ $review['created_at'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
