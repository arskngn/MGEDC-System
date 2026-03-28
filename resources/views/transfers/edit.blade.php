<x-app-layout>
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            productQuery: '',
            searchResults: [],
            searchLoading: false,
            searchTimer: null,
            warehouseId: '{{ old('from_warehouse_id', $transfer->from_warehouse_id) }}',
            items: {{ json_encode($transfer->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->sku,
                'unit_label' => $item->unit_label,
                'in_stock' => $item->from_stock,
                'quantity' => $item->quantity,
            ])->values()) }},
            async searchProducts() {
                clearTimeout(this.searchTimer);
                const q = this.productQuery.trim();
                if (q.length < 1) { this.searchResults = []; return; }
                this.searchTimer = setTimeout(async () => {
                    this.searchLoading = true;
                    try {
                        const r = await fetch('{{ route('transfers.products.search') }}?q=' + encodeURIComponent(q) + '&warehouse_id=' + this.warehouseId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
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
                });
                this.productQuery = '';
                this.searchResults = [];
            },
            removeRow(i) { this.items.splice(i, 1); },
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Edit Transfer</h2>
            <a href="{{ route('transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('transfers.update', $transfer) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="transfer_date" value="{{ old('transfer_date', $transfer->transfer_date->format('Y-m-d')) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                    @error('transfer_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Warehouse <span class="text-red-500">*</span></label>
                    <select name="from_warehouse_id" x-model="warehouseId" required @change="searchResults = []" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]">
                        <option value="">Select One</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('from_warehouse_id', $transfer->from_warehouse_id) == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Warehouse <span class="text-red-500">*</span></label>
                    <select name="to_warehouse_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]">
                        <option value="">Select One</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('to_warehouse_id', $transfer->to_warehouse_id) == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <input type="hidden" name="tracking_no" value="{{ $transfer->tracking_no }}" />

            <div class="relative" @click.outside="searchResults = []">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
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
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">In Stock</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Quantity <span class="text-red-300">*</span></th>
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
                                    <div class="text-sm text-gray-600" x-text="`${item.in_stock} ${item.unit_label}`"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        :name="`items[${idx}][quantity]`" 
                                        x-model.number="item.quantity"
                                        placeholder="0" 
                                        class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-[#4634ff]"
                                    />
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
                <textarea name="note" rows="3" placeholder="Add any notes about this transfer..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-[#4634ff]">{{ old('note', $transfer->note) }}</textarea>
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
