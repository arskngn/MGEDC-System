<x-app-layout>
    <div x-data="dashboard({
        summary: {{ json_encode($summary) }},
        transactions: {{ json_encode($transactions) }},
        settings: {
            currency_symbol: '{{ $settings->currency_symbol ?? '$' }}',
            currency: '{{ $settings->currency ?? 'USD' }}',
            currency_format: '{{ $settings->currency_format ?? 'both' }}'
        }
    })" class="space-y-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Products -->
            <a href="{{ route('products.all') }}" class="bg-white overflow-hidden shadow-sm rounded-xl flex items-center p-6 hover:shadow-md transition-shadow group">
                <div class="flex-shrink-0 border-2 border-blue-500 rounded-xl p-3 bg-white">
                    <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-[13px] font-bold text-[#0a1233] opacity-80">Total Products</div>
                    <div class="text-2xl font-extrabold text-[#0a1233] mt-1" x-text="summary.total_products"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-[#4634ff] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- Total Customers -->
            <a href="{{ route('customers.index') }}" class="bg-white overflow-hidden shadow-sm rounded-xl flex items-center p-6 hover:shadow-md transition-shadow group">
                <div class="flex-shrink-0 border-2 border-green-500 rounded-xl p-3 bg-white">
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-[13px] font-bold text-[#0a1233] opacity-80">Total Customers</div>
                    <div class="text-2xl font-extrabold text-[#0a1233] mt-1" x-text="summary.total_customers"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-[#4634ff] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- Total Suppliers -->
            <a href="{{ route('suppliers.index') }}" class="bg-white overflow-hidden shadow-sm rounded-xl flex items-center p-6 hover:shadow-md transition-shadow group">
                <div class="flex-shrink-0 border-2 border-purple-500 rounded-xl p-3 bg-white">
                    <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-[13px] font-bold text-[#0a1233] opacity-80">Total Suppliers</div>
                    <div class="text-2xl font-extrabold text-[#0a1233] mt-1" x-text="summary.total_suppliers"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-[#4634ff] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- Total Categories -->
            <a href="{{ route('categories.index') }}" class="bg-white overflow-hidden shadow-sm rounded-xl flex items-center p-6 hover:shadow-md transition-shadow group">
                <div class="flex-shrink-0 border-2 border-orange-500 rounded-xl p-3 bg-white">
                    <svg class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-[13px] font-bold text-[#0a1233] opacity-80">Total Categories</div>
                    <div class="text-2xl font-extrabold text-[#0a1233] mt-1" x-text="summary.total_categories"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-[#4634ff] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Sales -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2">Sales</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('sales.all') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-green-500 transition-all group cursor-pointer">
                        <div class="bg-green-100 p-2 rounded-md mr-4 group-hover:bg-green-200 transition-colors">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold" x-text="transactions.sales.count"></div>
                            <div class="text-xs text-gray-500">Total Sales</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-green-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('sales.all') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-orange-500 transition-all group cursor-pointer">
                        <div class="bg-orange-100 p-2 rounded-md mr-4 group-hover:bg-orange-200 transition-colors">
                            <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold" x-text="formatCurrency(transactions.sales.total_amount)"></div>
                            <div class="text-xs text-gray-500">Total Sales Amount</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-orange-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('sales.return') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-red-500 transition-all group cursor-pointer">
                        <div class="bg-red-100 p-2 rounded-md mr-4 group-hover:bg-red-200 transition-colors">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold" x-text="transactions.sale_returns.count"></div>
                            <div class="text-xs text-gray-500">Total Sales Return</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-red-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('sales.return') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-indigo-500 transition-all group cursor-pointer">
                        <div class="bg-indigo-100 p-2 rounded-md mr-4 group-hover:bg-indigo-200 transition-colors">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold" x-text="formatCurrency(transactions.sale_returns.total_amount)"></div>
                            <div class="text-xs text-gray-500">Total Sales Return Amount</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Purchases -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2">Purchases</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('purchases.all') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-green-500 transition-all group cursor-pointer">
                        <div class="bg-green-100 p-2 rounded-md mr-4 group-hover:bg-green-200 transition-colors">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold" x-text="transactions.purchases.count"></div>
                            <div class="text-xs text-gray-500">Total Purchases</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-green-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('purchases.all') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-orange-500 transition-all group cursor-pointer">
                        <div class="bg-orange-100 p-2 rounded-md mr-4 group-hover:bg-orange-200 transition-colors">
                            <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold" x-text="formatCurrency(transactions.purchases.total_amount)"></div>
                            <div class="text-xs text-gray-500">Total Purchases Amount</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-orange-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('purchases.return') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-red-500 transition-all group cursor-pointer">
                        <div class="bg-red-100 p-2 rounded-md mr-4 group-hover:bg-red-200 transition-colors">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold" x-text="transactions.purchase_returns.count"></div>
                            <div class="text-xs text-gray-500">Total Purchases Return</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-red-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('purchases.return') }}" class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-indigo-500 transition-all group cursor-pointer">
                        <div class="bg-indigo-100 p-2 rounded-md mr-4 group-hover:bg-indigo-200 transition-colors">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold" x-text="formatCurrency(transactions.purchase_returns.total_amount)"></div>
                            <div class="text-xs text-gray-500">Total Purchases Return Amount</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 ml-auto group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Expiring Batches Alert -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-orange-500">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 p-2 bg-orange-100 rounded-lg">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 5v.01M12 3a9 9 0 100 18 9 9 0 000-18z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Expiring Batches Alert</h3>
                        @if($expiringBatches->isNotEmpty())
                            <p class="text-xs text-gray-500 mt-1">{{ $expiringBatches->count() }} batch(es) expiring within 30 days</p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">No batches expiring within 30 days</p>
                        @endif
                    </div>
                </div>
                <a href="{{ route('reports.stock') }}" class="inline-flex items-center px-3 py-1 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors">
                    View All
                    <svg class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            <div class="overflow-y-auto max-h-96 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                @if($expiringBatches->isNotEmpty())
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Product</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Batch #</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Warehouse</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Quantity</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Expiration</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($expiringBatches->take(5) as $item)
                                @php
                                    $batch = $item['batch'];
                                    $status = $item['status'];
                                    $daysLeft = $item['days_left'];
                                    
                                    $badgeClasses = [
                                        'urgent' => 'bg-red-100 text-red-800 border border-red-300',
                                        'warning' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                        'normal' => 'bg-orange-100 text-orange-800 border border-orange-300',
                                    ];
                                    
                                    $statusLabels = [
                                        'urgent' => "Expires in $daysLeft days",
                                        'warning' => "Expires in $daysLeft days",
                                        'normal' => "Expires in $daysLeft days",
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $batch->product->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $batch->batch_number }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $batch->warehouse->name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        {{ number_format($batch->quantity, 2) }} {{ $batch->product->unit?->short_name ?? 'Unit' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $batch->expiration_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $badgeClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$status] ?? 'Unknown' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium">No expiring batches</p>
                        <p class="text-sm">All batches are in good standing</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reports Placeholder -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Purchases & Sales Report</h3>
                    <div class="flex items-center space-x-2 text-sm text-gray-500 border rounded px-2 py-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>March 5, 2026 - March 19, 2026</span>
                    </div>
                </div>
                <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400 italic">
                    [ Purchases & Sales Chart Placeholder ]
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Sales & Sales Return Report</h3>
                    <div class="flex items-center space-x-2 text-sm text-gray-500 border rounded px-2 py-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>March 5, 2026 - March 19, 2026</span>
                    </div>
                </div>
                <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400 italic">
                    [ Sales & Sales Return Chart Placeholder ]
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function dashboard(initialData) {
            return {
                summary: initialData.summary,
                transactions: initialData.transactions,
                settings: initialData.settings,

                init() {
                    if (window.Echo) {
                        window.Echo.channel('sales')
                            .listen('SaleCreated', (e) => {
                                console.log('Sale Created:', e);
                                this.refreshStats();
                            })
                            .listen('SaleReturnCreated', (e) => {
                                console.log('Sale Return Created:', e);
                                this.refreshStats();
                            })
                            .listen('PurchaseCreated', (e) => {
                                console.log('Purchase Created:', e);
                                this.refreshStats();
                            })
                            .listen('PurchaseReturnCreated', (e) => {
                                console.log('Purchase Return Created:', e);
                                this.refreshStats();
                            });
                    }
                },

                async refreshStats() {
                    try {
                        const response = await axios.get("{{ route('dashboard.stats') }}");
                        this.summary = response.data.summary;
                        this.transactions = response.data.transactions;
                    } catch (error) {
                        console.error('Failed to refresh stats:', error);
                    }
                },

                formatCurrency(amount) {
                    const formatted = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount);

                    const { currency_symbol, currency, currency_format } = this.settings;

                    switch (currency_format) {
                        case 'symbol':
                            return currency_symbol + formatted;
                        case 'text':
                            return formatted + ' ' + currency;
                        case 'both':
                        default:
                            return currency_symbol + formatted + ' ' + currency;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
