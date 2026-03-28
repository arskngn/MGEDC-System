<x-app-layout>
    <div
        x-data="{
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
            bulkOpen: false,
            paymentOpen: false,
            paymentReturn: null,
            toggleMore(id, $event) {
                if (this.openMoreId === id) {
                    this.openMoreId = null;
                    return;
                }
                const r = $event.currentTarget.getBoundingClientRect();
                this.dropdownTop = (r.bottom + 4) + 'px';
                this.dropdownRight = (window.innerWidth - r.right) + 'px';
                this.openMoreId = id;
            },
            openPayment(r) {
                this.paymentReturn = r;
                this.paymentOpen = true;
                this.openMoreId = null;
                this.$nextTick(() => {
                    const inp = this.$el.querySelector('.payment-receiving-input');
                    if (inp) { inp.max = r.due; inp.value = ''; }
                });
            },
        }"
        @keydown.escape.window="paymentOpen = false; openMoreId = null; bulkOpen = false"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">All Purchase Return</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form
                    method="GET"
                    action="{{ route('purchases.return') }}"
                    x-data="purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) })"
                    x-ref="filterForm"
                    @keydown.escape.window="escapeClose()"
                    class="inline-flex flex-wrap items-center gap-2"
                >
                    @if($filterPurchaseId)
                        <input type="hidden" name="purchase_id" value="{{ $filterPurchaseId }}" />
                    @endif
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    <div class="relative flex w-full sm:w-[260px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    @include('components.date-range-picker')

                    @if(request()->hasAny(['search', 'date_from', 'date_to', 'purchase_id']))
                        <a href="{{ route('purchases.return') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                @if(auth()->user()->hasPermission('Download Purchase Return PDF') || auth()->user()->hasPermission('Download Purchase Return CSV'))
                    <div class="relative">
                        <button type="button" @click="bulkOpen = !bulkOpen; openMoreId = null" class="inline-flex items-center px-4 py-2 bg-[#48cf82] border border-[#48cf82] text-white rounded-md hover:bg-[#3dbd75] text-sm font-semibold shadow-sm">
                            Action
                            <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            x-show="bulkOpen"
                            @click.outside="bulkOpen = false"
                            x-cloak
                            class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-[200] py-1"
                        >
                            @if(auth()->user()->hasPermission('Download Purchase Return PDF'))
                                <a href="{{ route('purchases.return.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download PDF
                                </a>
                            @endif
                            @if(auth()->user()->hasPermission('Download Purchase Return CSV'))
                                <a href="{{ route('purchases.return.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Download CSV
                                </a>
                            @endif
                        </div>
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

        @if($filterPurchaseId)
            <div class="rounded-lg border border-[#5542ff]/30 bg-[#5542ff]/5 px-4 py-2 text-sm text-[#0a1233]">
                Filtered by purchase.
                <a href="{{ route('purchases.return') }}" class="font-semibold text-[#5542ff] hover:underline ml-1">Show all returns</a>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1100px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Invoice No. | Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Supplier | Mobile</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Warehouse | Total Amount</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Lessed | Receivable</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Received | Due</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($returns as $r)
                            @php
                                $phone = $r->purchase->supplier->phone;
                                $phoneDisp = $phone ? (\Illuminate\Support\Str::startsWith($phone, '+') ? $phone : '+'.$phone) : '—';
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-start gap-1.5">
                                        @if($r->due_amount > 0.009)
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0" title="Balance due"></span>
                                        @endif
                                        <div>
                                            <div class="text-sm font-bold text-[#2563eb]">{{ $r->return_invoice_no }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $r->return_date->format('d M, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-gray-900">{{ $r->purchase->supplier->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $phoneDisp }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-gray-900">{{ formatCurrency($r->subtotal) }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $r->warehouse->name }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-500">{{ formatCurrency($r->discount) }}</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ formatCurrency($r->receivable_amount) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-500">{{ formatCurrency($r->received_amount) }}</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ formatCurrency($r->due_amount) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap whitespace-nowrap">
                                        @if(auth()->user()->hasPermission('Edit Purchase Return'))
                                            <a href="{{ route('purchases.return.edit', $r) }}" class="inline-flex items-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                        @endif

                                        <div class="inline-flex">
                                            <button
                                                type="button"
                                                @click.stop="toggleMore({{ $r->id }}, $event)"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-white border border-sky-400 text-sky-600 rounded-md hover:bg-sky-50 text-[11px] font-bold"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 text-sky-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                                More
                                                <svg class="h-3 w-3 ml-0.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <template x-teleport="body">
                                                <div
                                                    x-show="openMoreId === {{ $r->id }}"
                                                    @click.outside="openMoreId = null"
                                                    x-cloak
                                                    class="fixed w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-1 text-left z-[200]"
                                                    :style="{ top: dropdownTop, right: dropdownRight }"
                                                >
                                                    @if(auth()->user()->hasPermission('Store Supplier Payment Receive') && $r->due_amount > 0.009)
                                                        <button
                                                            type="button"
                                                            @click="openPayment({ id: {{ $r->id }}, invoice: @js($r->return_invoice_no), supplier: @js($r->purchase->supplier->name), receivable: @js(number_format((float)$r->receivable_amount, 2, '.', '')), due: @js(number_format($r->due_amount, 2, '.', '')) })"
                                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                        >
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                            Receive Payment
                                                        </button>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('Download Purchase Return Invoice PDF'))
                                                        <a href="{{ route('purchases.return.invoice.pdf', $r) }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                            Download Details
                                                        </a>
                                                    @endif
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No purchase returns found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returns->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>

        <div x-show="paymentOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="paymentOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Receive Payment</h3>
                        <button type="button" @click="paymentOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div x-show="paymentReturn" x-cloak>
                        <form method="POST" class="space-y-4" x-bind:action="'{{ url('/purchases/return') }}/' + paymentReturn.id + '/payment'">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Invoice No.</label>
                                <input type="text" :value="paymentReturn.invoice" disabled class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Supplier</label>
                                <input type="text" :value="paymentReturn.supplier" disabled class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Receivable amount</label>
                                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                                    <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                                    <input type="text" :value="paymentReturn.receivable" disabled class="flex-1 px-3 py-2 bg-gray-100 text-sm text-gray-700" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Receiving amount <span class="text-red-500">*</span></label>
                                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                                    <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                                    <input type="number" name="receiving_amount" step="0.01" min="0.01" required class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] payment-receiving-input" placeholder="0.00" />
                                </div>
                                @error('receiving_amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full py-3 rounded-md bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
