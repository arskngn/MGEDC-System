<x-app-layout>
    <div
        x-data="{
            bulkOpen: false,
            filterDropdownOpen: false,
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
            submitSearch() {
                document.getElementById('search-form').submit();
            }
        }"
        @keydown.escape.window="filterDropdownOpen = false; bulkOpen = false"
        @scroll.window="if (filterDropdownOpen) filterDropdownOpen = false; if (bulkOpen) bulkOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Supplier Payments</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form id="search-form" ref="filterForm" method="GET" action="{{ route('supplier-payments.index') }}" class="inline-flex flex-wrap items-center gap-2" x-data="purchaseDateRange({ dateFrom: '{{ $startDate }}', dateTo: '{{ $endDate }}' })" @keydown.escape.window="escapeClose()">
                    <!-- Filter Dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="filterDropdownOpen = !filterDropdownOpen"
                            @click.outside="filterDropdownOpen = false"
                            class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff]/40 rounded-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm hover:bg-[#5542ff]/5 transition-colors"
                        >
                            @if($filter === 'paid_for_purchase')
                                Paid For Purchase
                            @elseif($filter === 'received_for_purchase_return')
                                Received For Purchase Return
                            @else
                                All
                            @endif
                            <svg class="h-4 w-4 ml-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="filterDropdownOpen"
                            x-cloak
                            class="absolute top-full left-0 mt-1 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-50"
                        >
                            <a href="{{ route('supplier-payments.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                All
                            </a>
                            <a href="{{ route('supplier-payments.index', ['filter' => 'paid_for_purchase']) }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                Paid For Purchase
                            </a>
                            <a href="{{ route('supplier-payments.index', ['filter' => 'received_for_purchase_return']) }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                Received For Purchase Return
                            </a>
                        </div>
                    </div>

                    <!-- Date Range Picker -->
                    <input type="hidden" name="start_date" x-model="dateFrom">
                    <input type="hidden" name="end_date" x-model="dateTo">
                    <div class="relative w-full sm:w-[260px] shrink-0" @click.outside="close()">
                        <div class="flex rounded-md border border-[#5542ff]/40 overflow-hidden bg-white focus-within:ring-1 focus-within:ring-[#5542ff] focus-within:border-[#5542ff]">
                            <input
                                type="text"
                                readonly
                                :value="displayPlaceholder()"
                                @click="toggle()"
                                placeholder="Start Date - End Date"
                                class="flex-1 min-w-0 pl-3 pr-2 py-2 text-sm text-gray-600 cursor-pointer bg-white border-0 focus:ring-0"
                            />
                            <button type="button" @click="submitSearch()" class="px-3 bg-[#5542ff] text-white hover:bg-[#4736d6] shrink-0" title="Search with current filters">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>

                        {{-- Dropdown panel --}}
                        <div
                            x-show="open"
                            x-cloak
                            x-transition
                            class="absolute z-[45] mt-1.5 rounded-lg border border-gray-200 bg-white shadow-xl origin-top-right"
                            :class="customMode ? 'right-0 w-[min(100vw-1.5rem,620px)]' : 'left-0 w-[180px]'"
                        >
                            {{-- Caret pointing up --}}
                            <div class="absolute -top-[5px] w-2.5 h-2.5 bg-white border-t border-l border-gray-200 rotate-45 z-0" :class="customMode ? 'right-6' : 'left-6'"></div>

                            <div class="relative flex bg-white w-full rounded-t-lg z-10" :class="customMode ? 'flex-col sm:flex-row' : 'rounded-b-lg'">
                                {{-- Presets column --}}
                                <div class="shrink-0 py-2" :class="customMode ? 'w-full sm:w-[160px] border-b sm:border-b-0 sm:border-r border-gray-100' : 'w-full'">
                                    @foreach ([
                                        ['id' => 'today', 'label' => 'Today'],
                                        ['id' => 'yesterday', 'label' => 'Yesterday'],
                                        ['id' => 'last7', 'label' => 'Last 7 Days'],
                                        ['id' => 'last15', 'label' => 'Last 15 Days'],
                                        ['id' => 'last30', 'label' => 'Last 30 Days'],
                                        ['id' => 'thisMonth', 'label' => 'This Month'],
                                        ['id' => 'lastMonth', 'label' => 'Last Month'],
                                        ['id' => 'last6', 'label' => 'Last 6 Months'],
                                        ['id' => 'thisYear', 'label' => 'This Year'],
                                        ['id' => 'custom', 'label' => 'Custom Range'],
                                    ] as $preset)
                                        <button
                                            type="button"
                                            @click="selectPreset('{{ $preset['id'] }}')"
                                            class="w-full text-left px-4 py-1.5 text-sm transition-colors"
                                            :class="activePreset === '{{ $preset['id'] }}' ? 'bg-[#5542ff] text-white font-medium' : 'text-gray-600 hover:bg-gray-50'"
                                        >
                                            {{ $preset['label'] }}
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Calendars (custom range only) --}}
                                <div class="flex-1 p-3 min-w-0" x-show="customMode" x-cloak>
                                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                        {{-- Left calendar --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-2">
                                                <button type="button" @click="prevMonths()" class="p-1 rounded hover:bg-gray-100 text-gray-600" aria-label="Previous month">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                                </button>
                                                <div class="flex items-center gap-1">
                                                    <select class="text-xs font-semibold text-[#0a1233] border border-gray-200 rounded px-1 py-0.5 bg-white cursor-pointer"
                                                        x-model.number="viewMonth"
                                                    >
                                                        <template x-for="m in 12" :key="'lm'+m">
                                                            <option :value="m-1" x-text="new Date(2000, m-1, 1).toLocaleString('en-US',{month:'short'})"></option>
                                                        </template>
                                                    </select>
                                                    <select class="text-xs font-semibold text-[#0a1233] border border-gray-200 rounded px-1 py-0.5 bg-white cursor-pointer"
                                                        x-model.number="viewYear"
                                                    >
                                                        <template x-for="y in yearOptions()" :key="'ly'+y">
                                                            <option :value="y" x-text="y"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <span class="w-6"></span>
                                            </div>
                                            <div class="grid grid-cols-7 gap-0.5 text-center text-[10px] font-semibold text-gray-400 mb-1">
                                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                            </div>
                                            <template x-for="(week, wi) in leftGrid()" :key="'lw'+wi">
                                                <div class="grid grid-cols-7 gap-0.5">
                                                    <template x-for="(d, di) in week" :key="'ld'+wi+'-'+di">
                                                        <div @click="clickDay(d)" :class="dayClass(d)">
                                                            <span x-text="d ? d.getDate() : ''"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>

                                        {{-- Right calendar --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="w-6"></span>
                                                <div class="flex items-center gap-1">
                                                    <select class="text-xs font-semibold text-[#0a1233] border border-gray-200 rounded px-1 py-0.5 bg-white cursor-pointer"
                                                        @change="
                                                            let rm = parseInt($event.target.value);
                                                            let diff = rm - ((viewMonth + 1) > 11 ? 0 : viewMonth + 1);
                                                            if (diff !== 0) {
                                                                let nm = viewMonth + diff;
                                                                if (nm < 0) { viewYear += Math.floor(nm / 12); nm = ((nm % 12) + 12) % 12; }
                                                                else if (nm > 11) { viewYear += Math.floor(nm / 12); nm = nm % 12; }
                                                                viewMonth = nm;
                                                            }
                                                        "
                                                        :value="rightCalYearMonth().month"
                                                    >
                                                        <template x-for="m in 12" :key="'rm'+m">
                                                            <option :value="m-1" x-text="new Date(2000, m-1, 1).toLocaleString('en-US',{month:'short'})"></option>
                                                        </template>
                                                    </select>
                                                    <select class="text-xs font-semibold text-[#0a1233] border border-gray-200 rounded px-1 py-0.5 bg-white cursor-pointer"
                                                        @change="
                                                            let ry = parseInt($event.target.value);
                                                            let cy = rightCalYearMonth().year;
                                                            if (ry !== cy) { viewYear += (ry - cy); }
                                                        "
                                                        :value="rightCalYearMonth().year"
                                                    >
                                                        <template x-for="y in yearOptions()" :key="'ry'+y">
                                                            <option :value="y" x-text="y"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <button type="button" @click="nextMonths()" class="p-1 rounded hover:bg-gray-100 text-gray-600" aria-label="Next month">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-7 gap-0.5 text-center text-[10px] font-semibold text-gray-400 mb-1">
                                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                            </div>
                                            <template x-for="(week, wi) in rightGrid()" :key="'rw'+wi">
                                                <div class="grid grid-cols-7 gap-0.5">
                                                    <template x-for="(d, di) in week" :key="'rd'+wi+'-'+di">
                                                        <div @click="clickDay(d)" :class="dayClass(d)">
                                                            <span x-text="d ? d.getDate() : ''"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer: only when customMode --}}
                            <div class="relative z-10 flex items-center justify-between gap-2 px-3 py-2 border-t border-gray-100 bg-gray-50 rounded-b-lg"
                                 x-show="customMode" x-cloak>
                                <span class="text-xs text-gray-600 font-medium truncate" x-text="footerLabel()"></span>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="clearRange()" class="px-3 py-1 text-sm font-medium text-gray-700 hover:text-black">Clear</button>
                                    <button type="button" @click="applyFooter()" class="px-4 py-1 text-sm font-semibold rounded-md bg-[#5542ff] text-white hover:bg-[#4736d6]">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            x-model="searchValue"
                            value="{{ $search }}"
                            placeholder="Search..."
                            title="Search by supplier name, invoice no., or TRX"
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
                @if(auth()->user()->hasPermission('Download Supplier Payment PDF') || auth()->user()->hasPermission('Download Supplier Payment CSV'))
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
                                @if(auth()->user()->hasPermission('Download Supplier Payment PDF'))
                                    <a href="{{ route('supplier-payments.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download PDF
                                    </a>
                                @endif

                                @if(auth()->user()->hasPermission('Download Supplier Payment CSV'))
                                    <a href="{{ route('supplier-payments.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
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
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Supplier</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">TRX</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Reason</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payments as $i => $payment)
                            @php
                                $isPayment = $payment['type'] === 'payment';
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">{{ $i + 1 }}</td>
                                <td class="px-4 py-4 align-middle text-sm font-bold text-[#2563eb]">{{ $payment['invoice_no'] }}</td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    {{ $payment['date']->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-medium text-gray-700">
                                    {{ $payment['supplier']->name }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-mono text-gray-600">
                                    {{ $payment['trx'] }}
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isPayment ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $payment['reason'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold {{ $isPayment ? 'text-red-600' : 'text-green-600' }}">
                                    {{ formatCurrency($payment['amount']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No supplier payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
