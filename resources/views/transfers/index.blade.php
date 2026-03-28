<x-app-layout>
    <div
        x-data="{
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
            bulkOpen: false,
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
        }"
        @keydown.escape.window="bulkOpen = false; openMoreId = null"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">All Transfers</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
            <form
                method="GET"
                action="{{ route('transfers.index') }}"
                x-data="purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) })"
                x-ref="filterForm"
                @keydown.escape.window="escapeClose()"
                class="inline-flex flex-wrap items-center gap-2"
            >
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

                @if(request()->hasAny(['search', 'date_from', 'date_to']))
                    <a href="{{ route('transfers.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                @endif
            </form>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if(auth()->user()->hasPermission('Create Transfer'))
                    <a href="{{ route('transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                        <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New
                    </a>
                @endif

                @if(auth()->user()->hasPermission('Download Transfer PDF') || auth()->user()->hasPermission('Download Transfer CSV'))
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
                            @if(auth()->user()->hasPermission('Download Transfer PDF'))
                                <a href="{{ route('transfers.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download PDF
                                </a>
                            @endif
                            @if(auth()->user()->hasPermission('Download Transfer CSV'))
                                <a href="{{ route('transfers.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download CSV
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1200px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">S.N.</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Tracking No.</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">From</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">To</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Products</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($transfers as $i => $transfer)
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-gray-900">{{ ($transfers->currentPage() - 1) * $transfers->perPage() + $i + 1 }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-gray-900">{{ $transfer->tracking_no }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-600">{{ $transfer->transfer_date->format('d M, Y') }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-600">{{ $transfer->fromWarehouse->name }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-600">{{ $transfer->toWarehouse->name }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-600">{{ count($transfer->items) }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-right align-top">
                                    <div class="flex justify-end gap-2 flex-wrap">
                                        @if(auth()->user()->hasPermission('Edit Transfer'))
                                            <a href="{{ route('transfers.edit', $transfer) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-300 rounded hover:bg-blue-100 transition-colors text-xs font-semibold">
                                                <svg class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Edit
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('Download Transfer PDF'))
                                            <a href="{{ route('transfers.pdf', $transfer) }}" class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-600 border border-green-300 rounded hover:bg-green-100 transition-colors text-xs font-semibold">
                                                <svg class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                Download
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <p>No transfers found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-center">
            {{ $transfers->links() }}
        </div>
    </div>
</x-app-layout>