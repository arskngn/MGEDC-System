<x-app-layout>
    <div
        x-data="{
            bulkOpen: false,
            importOpen: {{ $errors->has('csv_file') ? 'true' : 'false' }},
            actionTop: '0px',
            actionRight: '0px',
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            },
        }"
        @keydown.escape.window="importOpen = false; bulkOpen = false"
        @scroll.window="if (bulkOpen) bulkOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Products</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="{{ route('products.all') }}" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            title="Search by product name, SKU, category, brand, or unit"
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('products.all') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(auth()->user()->hasPermission('Add Product'))
                        <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('Download Product PDF') || auth()->user()->hasPermission('Download Product CSV') || auth()->user()->hasPermission('Product Import'))
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
                                    @if(auth()->user()->hasPermission('Download Product PDF'))
                                        <a href="{{ route('products.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                            Download PDF
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('Download Product CSV'))
                                        <a href="{{ route('products.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Download CSV
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('Product Import'))
                                        <button type="button" @click="importOpen = true; bulkOpen = false" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                            Import CSV
                                        </button>
                                    @endif
                                </div>
                            </template>
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
                <table class="w-full text-center border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name | SKU</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Category | Brand</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Stock</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Total Sale | Alert Qty</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Unit</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Expiration</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($products as $product)
                            @php
                                $unitLabel = $product->unit->short_name ?? $product->unit->name;
                                $stockStr = rtrim(rtrim(number_format((float) $product->current_stock, 4, '.', ''), '0'), '.') ?: '0';
                                $low = (float) $product->current_stock <= (float) $product->alert_quantity;
                                
                                // Get expiration status
                                $firstBatch = $product->batches()
                                    ->where('expiration_date', '>=', now())
                                    ->orderBy('expiration_date')
                                    ->first();
                                $expiredBatch = $product->batches()
                                    ->where('expiration_date', '<', now())
                                    ->orderBy('expiration_date', 'desc')
                                    ->first();
                                    
                                $expirationStatus = null;
                                if ($expiredBatch) {
                                    $expirationStatus = ['type' => 'expired', 'date' => $expiredBatch->expiration_date];
                                } elseif ($firstBatch && now()->diffInDays($firstBatch->expiration_date) < 5) {
                                    $expirationStatus = ['type' => 'urgent', 'date' => $firstBatch->expiration_date];
                                } elseif ($firstBatch && now()->diffInDays($firstBatch->expiration_date) < 30) {
                                    $expirationStatus = ['type' => 'warning', 'date' => $firstBatch->expiration_date];
                                } elseif ($firstBatch) {
                                    $expirationStatus = ['type' => 'normal', 'date' => $firstBatch->expiration_date];
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        @if($low)
                                            <span class="inline-block w-2 h-2 rounded-full bg-orange-500 shrink-0" title="At or below alert quantity"></span>
                                        @endif
                                        <div class="flex flex-col items-center max-w-[240px] mx-auto">
                                            <div class="text-sm font-bold text-[#5542ff] leading-snug break-words">{{ $product->name }}</div>
                                            <div class="text-xs font-normal text-gray-500 mt-1">{{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-center justify-center gap-1">
                                        <div class="text-sm font-normal text-gray-600">{{ $product->category?->name ?? '—' }}</div>
                                        <div class="text-sm font-bold text-[#5542ff]">{{ $product->brand?->name ?? '—' }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-normal text-gray-600">{{ $stockStr }}</td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="text-sm font-normal text-gray-600">{{ $product->total_sold }}</div>
                                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-xs font-semibold border border-orange-400 text-orange-600 bg-white">
                                            {{ rtrim(rtrim(number_format((float) $product->alert_quantity, 4, '.', ''), '0'), '.') ?: '0' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-normal text-gray-600">{{ $unitLabel }}</td>
                                <td class="px-4 py-4 align-middle text-center">
                                    @if($expirationStatus)
                                        @php
                                            $badgeClasses = [
                                                'expired' => 'bg-red-100 text-red-800 border border-red-300',
                                                'urgent' => 'bg-orange-100 text-orange-800 border border-orange-300',
                                                'warning' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                                'normal' => 'bg-green-100 text-green-800 border border-green-300',
                                            ];
                                            $labels = [
                                                'expired' => 'Expired',
                                                'urgent' => 'Urgent',
                                                'warning' => 'Warning',
                                                'normal' => 'Normal',
                                            ];
                                        @endphp
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $badgeClasses[$expirationStatus['type']] }}">
                                                {{ $labels[$expirationStatus['type']] }}
                                            </span>
                                            <div class="text-xs text-gray-600">{{ $expirationStatus['date']->format('M d, Y') }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex justify-center">
                                        @if(auth()->user()->hasPermission('Product Edit'))
                                            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold">
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        {{-- Import modal --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Product</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-950 text-xs p-3 mb-4 space-y-1">
                        <p class="font-semibold">CSV must match the sample: same columns and order.</p>
                        <p>Required: <strong>name</strong>, <strong>category</strong>, <strong>sku</strong>, <strong>brand</strong>, <strong>unit</strong>, <strong>alert_quantity</strong>. Optional: <strong>note</strong>.</p>
                        <p><strong>name</strong> and <strong>sku</strong> must be unique. Category, brand, and unit must match names (or unit short name) already in the system.</p>
                        <p>Fix errors in your file and re-import. Large files may need higher PHP time/memory limits.</p>
                        <p><strong>Comma</strong> or <strong>semicolon</strong> separators work (e.g. Excel “CSV UTF-8”).</p>
                    </div>

                    <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select File <span class="text-red-500">*</span></label>
                            <input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-[#5542ff]/10 file:text-[#5542ff] file:font-semibold" />
                            <p class="text-xs text-gray-500 mt-1">Supported files: <strong>csv</strong></p>
                            @error('csv_file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <p class="text-sm text-gray-600">
                            Download sample template:
                            <a href="{{ route('products.import.sample') }}" class="text-[#5542ff] font-semibold hover:underline">product.csv</a>
                        </p>
                        <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                            Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
