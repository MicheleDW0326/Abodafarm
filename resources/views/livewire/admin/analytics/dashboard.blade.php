<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Analytics</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Real-time business insights and performance metrics</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Date Range Filter -->
                    <select wire:model.live="dateRange" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last_7_days">Last 7 Days</option>
                        <option value="last_30_days">Last 30 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom Range</option>
                    </select>

                    @if($dateRange === 'custom')
                        <input type="date" wire:model.live="startDate" class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:text-white">
                        <input type="date" wire:model.live="endDate" class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:text-white">
                    @endif

                    <!-- Export Buttons -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.analytics.export', ['type' => 'sales', 'format' => 'pdf', 'range' => $dateRange]) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                            PDF
                        </a>
                        <a href="{{ route('admin.analytics.export', ['type' => 'sales', 'format' => 'excel', 'range' => $dateRange]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition">
                            Excel
                        </a>
                        <a href="{{ route('admin.analytics.export', ['type' => 'sales', 'format' => 'csv', 'range' => $dateRange]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                            CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- KPI Cards Row 1 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($kpiCards['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Today:</span>
                    <span class="ml-1 text-gray-600 dark:text-gray-400">Rp {{ number_format($kpiCards['today_revenue'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Today's Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Revenue</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($kpiCards['today_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-blue-600 dark:text-blue-400 font-medium">Monthly:</span>
                    <span class="ml-1 text-gray-600 dark:text-gray-400">Rp {{ number_format($kpiCards['monthly_revenue'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($kpiCards['total_orders']) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3 text-sm">
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ $kpiCards['completed_orders'] }} Completed</span>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $kpiCards['pending_orders'] }} Pending</span>
                </div>
            </div>

            <!-- Active Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Customers</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($kpiCards['active_customers']) }}</p>
                    </div>
                    <div class="p-3 bg-teal-100 dark:bg-teal-900/30 rounded-full">
                        <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-teal-600 dark:text-teal-400 font-medium">Growth:</span>
                    <span class="ml-1 text-gray-600 dark:text-gray-400">{{ $customerGrowth['growth'] >= 0 ? '+' : '' }}{{ $customerGrowth['growth'] }}%</span>
                </div>
            </div>
        </div>

        <!-- KPI Cards Row 2 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Completed Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($kpiCards['completed_orders']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Cancelled Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Cancelled</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($kpiCards['cancelled_orders']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($kpiCards['pending_orders']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Farmers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Active Farmers</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($kpiCards['active_farmers']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Monthly Revenue</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($kpiCards['monthly_revenue'] / 1000000, 1) }}M</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Chart Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Sales Analytics</h2>
                    <div class="flex items-center gap-2">
                        <select wire:model.live="salesPeriod" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:text-white">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        <select wire:model.live="chartType" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:text-white">
                            <option value="revenue">Revenue</option>
                            <option value="orders">Orders</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div id="sales-chart" class="h-80"></div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Selling Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Selling Products</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($topProducts as $index => $product)
                        <div class="p-4 flex items-center gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                @if($product['image'])
                                    <img src="{{ asset('storage/' . $product['image']) }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product['category'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $product['quantity'] }} sold</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($product['revenue'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No sales data available
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Top Farmers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Performing Farmers</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($topFarmers as $index => $farmer)
                        <div class="p-4 flex items-center gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-shrink-0 w-12 h-12 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                                @if($farmer['image'])
                                    <img src="{{ asset('storage/' . $farmer['image']) }}" alt="{{ $farmer['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $farmer['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $farmer['shop_name'] }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $farmer['rating'] ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($farmer['revenue'] / 1000000, 1) }}M</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No farmer data available
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Three Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Top Buyers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Buyers</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($topBuyers as $index => $buyer)
                        <div class="p-4 flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                                <span class="text-xs font-bold text-teal-600 dark:text-teal-400">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $buyer['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $buyer['order_count'] }} orders</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($buyer['total_spent'] / 1000000, 1) }}M</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            No buyer data available
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                        Low Stock Alert
                    </h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($lowStockProducts as $product)
                        <div class="p-4 flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                @if($product['image'])
                                    <img src="{{ asset('storage/' . $product['image']) }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product['category'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold {{ $product['stock'] <= 5 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                    {{ $product['stock'] }} left
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            All products well stocked
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        Out of Stock
                    </h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($outOfStockProducts as $product)
                        <div class="p-4 flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                @if($product['image'])
                                    <img src="{{ asset('storage/' . $product['image']) }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product['category'] }}</p>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    Out
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            All products in stock
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl shadow-lg p-6 text-white">
            <h2 class="text-xl font-bold mb-6">Financial Summary</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                <div>
                    <p class="text-emerald-100 text-sm">Gross Revenue</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($financialSummary['gross_revenue'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-emerald-100 text-sm">Discount Total</p>
                    <p class="text-2xl font-bold">-Rp {{ number_format($financialSummary['discount_total'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-emerald-100 text-sm">Promotion Cost</p>
                    <p class="text-2xl font-bold">-Rp {{ number_format($financialSummary['promotion_cost'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-emerald-100 text-sm">Shipping Revenue</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($financialSummary['shipping_revenue'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                    <p class="text-emerald-100 text-sm">Net Revenue</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($financialSummary['net_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Booking Stats & New Customers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Booking Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Booking Statistics</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $bookingStats['total'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Bookings</p>
                        </div>
                        <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $bookingStats['completed'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                            <span class="font-medium text-amber-600 dark:text-amber-400">{{ $bookingStats['pending'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Cancelled</span>
                            <span class="font-medium text-red-600 dark:text-red-400">{{ $bookingStats['cancelled'] }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-3">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Total Amount</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($bookingStats['total_amount'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">New Customers</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($newCustomers as $customer)
                        <div class="p-4 flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ substr($customer['name'], 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $customer['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $customer['email'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $customer['joined_at']->diffForHumans() }}</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($customer['total_spent'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            No new customers in this period
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    initChart();

    Livewire.hook('message.processed', () => {
        initChart();
    });
});

function initChart() {
    const salesData = @json($salesData);
    const chartType = @json($chartType);

    const labels = salesData.map(d => d.label || d.date || d.week || d.year);
    const values = salesData.map(d => chartType === 'orders' ? d.orders : d.revenue);
    const seriesName = chartType === 'orders' ? 'Orders' : 'Revenue';

    const options = {
        series: [{
            name: seriesName,
            data: values
        }],
        chart: {
            type: 'area',
            height: 320,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: false,
                    reset: true
                }
            },
            fontFamily: 'Inter, sans-serif'
        },
        colors: ['#10b981', '#3b82f6'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: labels,
            labels: {
                style: {
                    colors: '#9ca3af',
                    fontSize: '11px'
                },
                rotate: labels.length > 15 ? -45 : 0
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#9ca3af',
                    fontSize: '11px'
                },
                formatter: function (val) {
                    if (chartType === 'revenue') {
                        if (val >= 1000000) {
                            return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
                        } else if (val >= 1000) {
                            return 'Rp ' + (val / 1000).toFixed(0) + 'K';
                        }
                        return 'Rp ' + val;
                    }
                    return val;
                }
            }
        },
        grid: {
            borderColor: '#f3f4f6',
            strokeDashArray: 4,
            xaxis: {
                lines: {
                    show: false
                }
            }
        },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function (val) {
                    if (chartType === 'revenue') {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                    return val + ' orders';
                }
            }
        }
    };

    const chartEl = document.querySelector('#sales-chart');
    if (chartEl) {
        const existingChart = ApexCharts.getChartByID('salesChart');
        if (existingChart) {
            existingChart.destroy();
        }

        const chart = new ApexCharts(chartEl, options);
        chart.render();
    }
}
</script>
@endpush
