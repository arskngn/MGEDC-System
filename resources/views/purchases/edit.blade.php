<x-app-layout>
    @php
        $initialItems = $purchase->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'name' => $i->product_name,
            'sku' => $i->sku,
            'unit_label' => $i->unit_label,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
        ])->values()->all();
    @endphp
    {{-- JSON must not live inside x-data="..." — inner " characters break the HTML attribute and render JS as text --}}
    <script type="application/json" id="purchase-initial-items">@json($initialItems)</script>
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            productQuery: '',
            searchResults: [],
            searchTimer: null,
            items: [],
            discount: {{ old('discount', $purchase->discount) }},
            hasReturns: @json($hasReturns),
            init() {
                const el = document.getElementById('purchase-initial-items');
                if (el && el.textContent) {
                    this.items = JSON.parse(el.textContent);
                }
            },
            async searchProducts() {
                clearTimeout(this.searchTimer);
                const q = this.productQuery.trim();
                this.searchTimer = setTimeout(async () => {
                    try {
                        const r = await fetch('{{ route('purchases.products.search') }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        this.searchResults = await r.json();
                    } catch (e) { this.searchResults = []; }
                }, 250);
            },
            addProduct(p) {
                if (this.hasReturns) return;
                if (this.items.find(i => i.product_id === p.id)) return;
                this.items.push({
                    product_id: p.id,
                    name: p.name,
                    sku: p.sku,
                    unit_label: p.unit_label,
                    quantity: 1,
                    unit_price: p.default_purchase_price,
                });
                this.productQuery = '';
                this.searchResults = [];
            },
            removeRow(i) { if (!this.hasReturns) this.items.splice(i, 1); },
            rowTotal(i) {
                const r = this.items[i];
                return (parseFloat(r.quantity) || 0) * (parseFloat(r.unit_price) || 0);
            },
            get subtotal() {
                return this.items.reduce((a, _, i) => a + this.rowTotal(i), 0);
            },
            get payable() { return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0)); },
        }"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Edit Purchase</h2>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()->hasPermission('Store Purchase Return'))
                    <a href="{{ route('purchases.return.create', $purchase) }}" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-lg text-sm font-semibold hover:bg-[#5542ff]/5">
                        Purchase return
                    </a>
                @endif
                <a href="{{ route('purchases.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back
                </a>
            </div>
        </div>

        @if($hasReturns)
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 flex gap-3 items-start">
                <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div class="text-sm">
                    <p class="font-bold text-red-800">Some products have been returned from this purchase</p>
                    <p class="text-red-700 mt-1">You cannot edit a purchase after any quantity has been returned.</p>
                    <a href="{{ route('purchases.return', ['purchase_id' => $purchase->id]) }}" class="text-[#2563eb] font-medium hover:underline mt-2 inline-block">View return details</a>
                </div>
            </div>
        @endif

        <form action="{{ route('purchases.update', $purchase) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice No. <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_no" value="{{ old('invoice_no', $purchase->invoice_no) }}" :disabled="hasReturns" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm disabled:bg-gray-100" />
                    @error('invoice_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" :disabled="hasReturns" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm disabled:bg-gray-100">
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" :disabled="hasReturns" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm disabled:bg-gray-100" />
                    @error('purchase_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" :disabled="hasReturns" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm disabled:bg-gray-100">
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('warehouse_id', $purchase->warehouse_id) == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="relative" @click.outside="searchResults = []">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-red-500">*</span></label>
                <div class="flex rounded-lg border border-gray-200 overflow-hidden" :class="hasReturns ? 'opacity-50 pointer-events-none' : ''">
                    <span class="px-3 py-2 bg-gray-100 text-gray-400"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
                    <input type="text" x-model="productQuery" @input="searchProducts()" @click="searchProducts()" autocomplete="off" placeholder="Product Name or SKU" class="flex-1 px-3 py-2 text-sm focus:outline-none" />
                </div>
                <div x-show="searchResults.length && !hasReturns" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                    <template x-for="p in searchResults" :key="p.id">
                        <button type="button" @click="addProduct(p)" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 border-b border-gray-50 last:border-0">
                            <span x-text="p.name"></span>
                            <span class="text-gray-400 text-xs ml-2" x-text="p.sku"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#0a1233] text-white">
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Quantity <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Price <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Total</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-4 py-2">
                                    <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id" />
                                    <span class="text-gray-700 bg-gray-50 rounded px-2 py-1 block" x-text="item.name"></span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.0001" min="0.0001" :name="'items['+index+'][quantity]'" x-model="item.quantity" :disabled="hasReturns" required class="w-24 rounded border border-gray-200 px-2 py-1 disabled:bg-gray-100" />
                                        <span class="text-xs text-gray-500" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex rounded border border-gray-200 overflow-hidden w-32">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs">{{ currencySymbol() }}</span>
                                        <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" :disabled="hasReturns" required class="flex-1 px-2 py-1 min-w-0 disabled:bg-gray-100" />
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex rounded border border-gray-200 bg-gray-50 overflow-hidden w-32">
                                        <span class="px-2 py-1 text-gray-500 text-xs">{{ currencySymbol() }}</span>
                                        <span class="px-2 py-1 font-medium" x-text="rowTotal(index).toFixed(2)"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" @click="removeRow(index)" :disabled="hasReturns" class="inline-flex items-center px-3 py-1.5 border border-red-500 text-red-500 rounded-md hover:bg-red-50 text-xs font-bold disabled:opacity-40 disabled:hover:bg-white">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="note" rows="5" :disabled="hasReturns" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm disabled:bg-gray-100">{{ old('note', $purchase->note) }}</textarea>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Price <span class="text-red-500">*</span></label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="subtotal.toFixed(2)" class="flex-1 px-3 py-2 bg-gray-50 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="number" name="discount" step="0.01" min="0" x-model="discount" :disabled="hasReturns" class="flex-1 px-3 py-2 text-sm disabled:bg-gray-100" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payable Amount</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="payable.toFixed(2)" class="flex-1 px-3 py-2 bg-gray-50 text-sm font-semibold" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly value="{{ number_format((float) $purchase->paid_amount, 2, '.', '') }}" class="flex-1 px-3 py-2 bg-gray-50 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Amount</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly value="{{ number_format($purchase->due_amount, 2, '.', '') }}" class="flex-1 px-3 py-2 bg-gray-50 text-sm font-semibold" />
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="hasReturns || items.length === 0" class="w-full py-3 rounded-lg bg-[#4634ff] text-white font-semibold hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Submit
            </button>
        </form>
    </div>
</x-app-layout>
