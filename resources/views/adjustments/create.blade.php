<x-app-layout>
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            productQuery: '',
            searchResults: [],
            searchLoading: false,
            searchTimer: null,
            items: [],
            warehouseId: '',
            async searchProducts() {
                clearTimeout(this.searchTimer);
                const q = this.productQuery.trim();
                if (q.length < 1) { this.searchResults = []; return; }
                if (!this.warehouseId) { alert('Please select a warehouse first.'); this.productQuery = ''; return; }
                this.searchTimer = setTimeout(async () => {
                    this.searchLoading = true;
                    try {
                        const r = await fetch(`{{ route('adjustments.products.search') }}?q=${encodeURIComponent(q)}&warehouse_id=${this.warehouseId}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
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
                    type: 'Added',
                    product_batch_id: '',
                    batches: p.batches || []
                });
                this.productQuery = '';
                this.searchResults = [];
            },
            removeRow(i) { this.items.splice(i, 1); },
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">New Adjustment</h2>
            <a href="{{ route('adjustments.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('adjustments.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking No. <span class="text-red-500">*</span></label>
                    <input type="text" name="tracking_no" value="{{ old('tracking_no', $trackingNo) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-100" readonly />
                    @error('tracking_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" x-model="warehouseId" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]">
                        <option value="">Select One</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="adjustment_date" value="{{ old('adjustment_date', now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                    @error('adjustment_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="relative" @click.outside="searchResults = []">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-red-500">*</span></label>
                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                    <span class="px-3 py-2 bg-gray-100 text-gray-400"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
                    <input type="text" x-model="productQuery" @input="searchProducts()" autocomplete="off" placeholder="Product Name or SKU" class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-0" />
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
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#0a1233] text-white">
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Batch</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Current Stock</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Stock - After Adjust</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Adjust Qty <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Type <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-center text-[11px] font-bold uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="bg-white hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="hidden" :name="`items[${idx}][product_id]`" :value="item.product_id" />
                                    <div class="text-sm font-semibold text-gray-900" x-text="item.name"></div>
                                    <div class="text-xs text-gray-500" x-text="item.sku"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <select :name="`items[${idx}][product_batch_id]`" x-model="item.product_batch_id" class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-[#4634ff]">
                                        <option value="">No Batch</option>
                                        <template x-for="b in item.batches" :key="b.id">
                                            <option :value="b.id" x-text="`${b.batch_number} (Exp: ${b.expiration_date}) - ${b.available} available`"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-600" x-text="`${item.in_stock} ${item.unit_label}`"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold" :class="item.type === 'Added' ? 'text-blue-600' : 'text-red-600'">
                                        <span x-text="item.type === 'Added' 
                                            ? (parseInt(item.in_stock) + parseInt(item.quantity)) 
                                            : Math.max(0, parseInt(item.in_stock) - parseInt(item.quantity))"></span>
                                        <span x-text="` ${item.unit_label}`"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input 
                                        type="number" 
                                        :name="`items[${idx}][quantity]`" 
                                        x-model.number="item.quantity"
                                        placeholder="0" 
                                        class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-[#4634ff]"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <select :name="`items[${idx}][type]`" x-model="item.type" class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-[#4634ff]">
                                        <option value="Added">Added</option>
                                        <option value="Removed">Removed</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeRow(idx)" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                <textarea name="note" rows="3" placeholder="Add any notes about this adjustment..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-[#4634ff]">{{ old('note') }}</textarea>
                @error('note')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-[#5542ff] text-white py-2.5 rounded-lg font-semibold hover:bg-[#4634ff] transition-colors">
                    Submit
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
