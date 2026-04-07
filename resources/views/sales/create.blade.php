<x-app-layout>
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            productQuery: '',
            searchResults: [],
            searchLoading: false,
            searchTimer: null,
            items: [],
            discount: {{ old('discount', 0) }},
            async searchProducts() {
                clearTimeout(this.searchTimer);
                const q = this.productQuery.trim();
                this.searchTimer = setTimeout(async () => {
                    this.searchLoading = true;
                    try {
                        const r = await fetch('{{ route('sales.products.search') }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        this.searchResults = await r.json();
                    } catch (e) { this.searchResults = []; }
                    this.searchLoading = false;
                }, 250);
            },
            addProduct(p) {
                if (this.items.find(i => i.product_id === p.id)) return;
                this.items.push({
                    product_id: p.id,
                    name: p.name,
                    sku: p.sku,
                    unit_label: p.unit_label,
                    in_stock: p.in_stock,
                    quantity: 1,
                    unit_price: p.default_sale_price,
                });
                this.productQuery = '';
                this.searchResults = [];
            },
            removeRow(i) { this.items.splice(i, 1); },
            rowTotal(i) {
                const r = this.items[i];
                return (parseFloat(r.quantity) || 0) * (parseFloat(r.unit_price) || 0);
            },
            get subtotal() {
                return this.items.reduce((a, _, i) => a + this.rowTotal(i), 0);
            },
            get receivable() { return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0)); },
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Add Sale</h2>
            <a href="{{ route('sales.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('sales.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice No. <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_no" value="{{ old('invoice_no', $invoiceNo) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-100" readonly />
                    @error('invoice_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                    <select name="customer_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]">
                        <option value="">Select One</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} {{ $c->phone ? '+'.$c->phone : '' }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="sale_date" value="{{ old('sale_date', now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                    @error('sale_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]">
                        <option value="">Select One</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="relative" @click.outside="searchResults = []">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-red-500">*</span></label>
                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                    <span class="px-3 py-2 bg-gray-100 text-gray-400"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
                    <input type="text" x-model="productQuery" @input="searchProducts()" @click="searchProducts()" autocomplete="off" placeholder="Product Name or SKU" class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-0" />
                </div>
                <div x-show="searchResults.length" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                    <template x-for="p in searchResults" :key="p.id">
                        <button type="button" @click="addProduct(p)" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 border-b border-gray-50 last:border-0">
                            <span x-text="p.name"></span>
                            <span class="text-gray-400 text-xs ml-2" x-text="p.sku"></span>
                            <span class="text-gray-400 text-xs ml-2">(Stock: <span x-text="p.in_stock"></span>)</span>
                        </button>
                    </template>
                </div>
                @error('items')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                @error('items.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#0a1233] text-white">
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">In Stock</th>
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
                                        <span class="text-gray-700 bg-gray-50 rounded px-2 py-1" x-text="item.in_stock"></span>
                                        <span class="text-xs text-gray-500" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.0001" min="0.0001" :name="'items['+index+'][quantity]'" x-model="item.quantity" required class="w-24 rounded border border-gray-200 px-2 py-1" />
                                        <span class="text-xs text-gray-500" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex rounded border border-gray-200 overflow-hidden w-32">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs">{{ currencySymbol() }}</span>
                                        <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" required class="flex-1 px-2 py-1 min-w-0" />
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex rounded border border-gray-200 bg-gray-50 overflow-hidden w-32">
                                        <span class="px-2 py-1 text-gray-500 text-xs">{{ currencySymbol() }}</span>
                                        <span class="px-2 py-1 font-medium" x-text="rowTotal(index).toFixed(2)"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" @click="removeRow(index)" class="inline-flex items-center px-3 py-1.5 border border-red-500 text-red-500 rounded-md hover:bg-red-50 text-xs font-bold">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Add products using the search field above.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="note" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" placeholder="Optional note">{{ old('note') }}</textarea>
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
                            <input type="number" name="discount" step="0.01" min="0" x-model="discount" class="flex-1 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                        </div>
                        @error('discount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Receivable Amount</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="receivable.toFixed(2)" class="flex-1 px-3 py-2 bg-gray-50 text-sm font-semibold" />
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="items.length === 0" class="w-full py-3 rounded-lg bg-[#4634ff] text-white font-semibold hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Submit
            </button>
        </form>
    </div>
</x-app-layout>
