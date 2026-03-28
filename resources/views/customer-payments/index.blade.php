<x-app-layout>
    <div
        x-data="{
            bulkOpen: false,
            filterOpen: false,
            actionTop: '0px',
            actionRight: '0px',
            filterValue: '{{ $filter }}',
            searchValue: '{{ $search }}',
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            },
            selectFilter(value) {
                this.filterValue = value;
                this.filterOpen = false;
                // Update the hidden input immediately
                const hiddenInput = document.querySelector('input[name="filter"]');
                if (hiddenInput) {
                    hiddenInput.value = value;
                }
                // Submit the form immediately
                const form = document.getElementById('search-form');
                const formData = new FormData(form);
                const params = new URLSearchParams();
                
                // Manually add all values to ensure they're included
                if (this.searchValue && this.searchValue.trim()) {
                    params.set('search', this.searchValue.trim());
                }
                if (value && value !== 'all') {
                    params.set('filter', value);
                }
                if (this.dateFrom) {
                    params.set('start_date', this.dateFrom);
                }
                if (this.dateTo) {
                    params.set('end_date', this.dateTo);
                }
                
                window.location.href = `{{ route('customer-payments.index') }}?${params.toString()}`;
            },
            submitSearch() {
                const form = document.getElementById('search-form');
                const formData = new FormData(form);
                const params = new URLSearchParams();
                
                // Manually add all values to ensure they're included
                if (this.searchValue && this.searchValue.trim()) {
                    params.set('search', this.searchValue.trim());
                }
                if (this.filterValue && this.filterValue !== 'all') {
                    params.set('filter', this.filterValue);
                }
                if (this.dateFrom) {
                    params.set('start_date', this.dateFrom);
                }
                if (this.dateTo) {
                    params.set('end_date', this.dateTo);
                }
                
                window.location.href = `{{ route('customer-payments.index') }}?${params.toString()}`;
            }
        }"
        @keydown.escape.window="filterOpen = false; bulkOpen = false"
        @scroll.window="if (filterOpen) filterOpen = false; if (bulkOpen) bulkOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Customer Payments</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form id="search-form" ref="filterForm" method="GET" action="{{ route('customer-payments.index') }}" class="inline-flex flex-wrap items-center gap-2">
                    <!-- Filter Dropdown -->
                    <div class="relative" @click.outside="filterOpen = false">
                        <button
                            type="button"
                            @click="filterOpen = !filterOpen"
                            class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff]/40 rounded-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm hover:bg-[#5542ff]/5 transition-colors"
                        >
                            @if($filter === 'received_from_customer')
                                Received From Customer
                            @elseif($filter === 'paid_for_sale_return')
                                Paid For Sale Return
                            @else
                                All
                            @endif
                            <svg class="h-4 w-4 ml-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="filterOpen"
                            x-cloak
                            class="absolute top-full left-0 mt-1 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-50"
                        >
                            <a href="{{ route('customer-payments.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                All
                            </a>
                            <a href="{{ route('customer-payments.index', ['filter' => 'received_from_customer']) }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                Received From Customer
                            </a>
                            <a href="{{ route('customer-payments.index', ['filter' => 'paid_for_sale_return']) }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                Paid For Sale Return
                            </a>
                        </div>
                    </div>

                    <!-- Date Range Picker -->
                    <x-date-range-picker 
                        :start-date="$startDate" 
                        :end-date="$endDate"
                        form-id="search-form"
                    />

                    <!-- Search Input -->
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            x-model="searchValue"
                            value="{{ $search }}"
                            placeholder="Search..."
                            title="Search by customer name, invoice no., or TRX"
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                        />
                        <button type="button" @click="submitSearch()" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Hidden filter input -->
                    <input type="hidden" name="filter" x-model="filterValue">
                </form>

                <!-- Action Dropdown -->
                @if(auth()->user()->hasPermission('Download Customer Payment PDF') || auth()->user()->hasPermission('Download Customer Payment CSV'))
                    <div class="inline-flex">
                        <button type="button" @click.stop="toggleAction($event)" class="inline-flex items-center px-4 py-2 bg-[#48cf82] border border-[#48cf82] text-white rounded-md hover:bg-[#3dbd75] text-sm font-semibold shadow-sm">
                            Action
                            <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <template x-teleport="body">
                            <div
                                x-show="bulkOpen"
                                @click.outside="bulkOpen = false"
                                x-cloak
                                class="fixed w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-[200]"
                                :style="{ top: actionTop, right: actionRight }"
                            >
                                @if(auth()->user()->hasPermission('Download Customer Payment PDF'))
                                    <a href="{{ route('customer-payments.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download PDF
                                    </a>
                                @endif

                                @if(auth()->user()->hasPermission('Download Customer Payment CSV'))
                                    <a href="{{ route('customer-payments.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Download CSV
                                    </a>
                                @endif
                            </div>
                        </template>
                    </div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <div class="font-semibold mb-1">Please fix the following:</div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[980px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Invoice No.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Date</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Customer</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">TRX</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Reason</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payments as $i => $payment)
                            @php
                                $isReceipt = $payment['type'] === 'receipt';
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">{{ $i + 1 }}</td>
                                <td class="px-4 py-4 align-middle text-sm font-bold text-[#2563eb]">{{ $payment['invoice_no'] }}</td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    {{ $payment['date']->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-medium text-gray-700">
                                    {{ $payment['customer']->name }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-mono text-gray-600">
                                    {{ $payment['trx'] }}
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isReceipt ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $payment['reason'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold {{ $isReceipt ? 'text-green-600' : 'text-red-600' }}">
                                    {{ formatCurrency($payment['amount']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No customer payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
