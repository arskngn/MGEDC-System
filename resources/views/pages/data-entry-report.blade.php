<x-app-layout>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">{{ $title }}</h2>
        </div>

        <!-- Activity Logs Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            @switch($reportType)
                                @case('purchase')
                                @case('purchase-return')
                                @case('sale')
                                @case('sale-return')
                                    <th class="px-6 py-4 text-left font-semibold">Invoice No.</th>
                                @break
                                @case('product')
                                    <th class="px-6 py-4 text-left font-semibold">Product Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">SKU</th>
                                @break
                                @case('customer')
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Mobile</th>
                                @break
                                @case('customer-payment')
                                    <th class="px-6 py-4 text-left font-semibold">TRX No.</th>
                                    <th class="px-6 py-4 text-left font-semibold">Customer</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                @break
                                @case('supplier')
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Mobile</th>
                                @break
                                @case('supplier-payment')
                                    <th class="px-6 py-4 text-left font-semibold">TRX No.</th>
                                    <th class="px-6 py-4 text-left font-semibold">Supplier</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                @break
                                @case('adjustment')
                                @case('transfer')
                                    <th class="px-6 py-4 text-left font-semibold">Tracking No.</th>
                                @break
                                @case('expense')
                                    <th class="px-6 py-4 text-left font-semibold">Expense Reason</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                @break
                            @endswitch
                            <th class="px-6 py-4 text-left font-semibold">Type</th>
                            <th class="px-6 py-4 text-left font-semibold">By</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($activities as $index => $activity)
                            <tr class="hover:bg-gray-50 transition-colors">
                                @switch($reportType)
                                    @case('purchase')
                                    @case('purchase-return')
                                    @case('sale')
                                    @case('sale-return')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            {{ $model?->invoice_no ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('product')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->sku ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('customer')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->mobile ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('customer-payment')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            #{{ $model?->id ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->sale?->customer?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            {{ number_format($model?->amount ?? 0, 2) }}
                                        </td>
                                    @break
                                    @case('supplier')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->mobile ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('supplier-payment')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            #{{ $model?->id ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->purchase?->supplier?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            {{ number_format($model?->amount ?? 0, 2) }}
                                        </td>
                                    @break
                                    @case('adjustment')
                                    @case('transfer')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            {{ $model?->tracking_no ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('expense')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->reason ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            {{ number_format($model?->amount ?? 0, 2) }}
                                        </td>
                                    @break
                                @endswitch
                                
                                <td class="px-6 py-4 text-gray-700">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        @if(strtoupper($activity->action) === 'CREATED')
                                            class="bg-green-100 text-green-800"
                                        @elseif(strtoupper($activity->action) === 'UPDATED')
                                            class="bg-blue-100 text-blue-800"
                                        @elseif(strtoupper($activity->action) === 'DELETED')
                                            class="bg-red-100 text-red-800"
                                        @endif>
                                        {{ strtoupper($activity->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $activity->user?->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $activity->created_at->format('Y-m-d H:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                                        </svg>
                                        <p class="font-medium">No activity records found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($activities->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
