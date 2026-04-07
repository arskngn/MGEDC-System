<x-app-layout>
    @php
        $returnItems = $sale->items->map(fn ($i) => [
            'sale_item_id' => $i->id,
            'product_id' => $i->product_id,
            'name' => $i->product_name,
            'sku' => $i->sku,
            'unit_label' => $i->unit_label,
            'sale_quantity' => (float) $i->quantity,
            'in_stock' => $i->product ? $i->product->current_stock : 0,
            'unit_price' => (float) $i->unit_price,
            'return_quantity' => 0,
        ])->values()->all();
    @endphp
    <script type="application/json" id="sale-return-items">@json($returnItems)</script>
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            items: [],
            discount: 0,
            restockingFee: 0,
            init() {
                const el = document.getElementById('sale-return-items');
                if (el && el.textContent) {
                    this.items = JSON.parse(el.textContent);
                }
            },
            rowTotal(i) {
                const r = this.items[i];
                return (parseFloat(r.return_quantity) || 0) * (parseFloat(r.unit_price) || 0);
            },
            get subtotal() {
                return this.items.reduce((a, _, i) => a + this.rowTotal(i), 0);
            },
            get payableToCustomer() { 
                return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0) - (parseFloat(this.restockingFee) || 0)); 
            },
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Sale Return</h2>
            <a href="{{ route('sales.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('sales.return.store', $sale) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice No. <span class="text-red-500">*</span></label>
                    <input type="text" value="{{ $sale->invoice_no }}" disabled class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-100" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                    <input type="text" value="{{ $sale->customer->name }}" disabled class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-100" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" value="{{ now()->format('Y-m-d') }}" disabled class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-100" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse <span class="text-red-500">*</span></label>
                    <input type="text" value="{{ $sale->warehouse->name }}" disabled class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-100" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#0a1233] text-white">
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Sale Qty</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">In Stock</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Return Qty <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Price</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-4 py-2">
                                    <input type="hidden" :name="'return_items['+index+'][sale_item_id]'" :value="item.sale_item_id" />
                                    <span class="text-gray-700" x-text="item.name"></span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-1">
                                        <span x-text="item.sale_quantity"></span>
                                        <span class="text-xs text-gray-500" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-1">
                                        <span x-text="item.in_stock"></span>
                                        <span class="text-xs text-gray-500" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.0001" min="0" :max="item.sale_quantity" :name="'return_items['+index+'][return_quantity]'" x-model="item.return_quantity" class="w-24 rounded border border-gray-200 px-2 py-1" />
                                        <span class="text-xs text-gray-500" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <span x-text="'{{ currencySymbol() }}' + parseFloat(item.unit_price).toFixed(2) + ' {{ \App\Models\GeneralSetting::first()?->currency ?? 'USD' }}'"></span>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <span x-text="'{{ currencySymbol() }}' + rowTotal(index).toFixed(2) + ' {{ \App\Models\GeneralSetting::first()?->currency ?? 'USD' }}'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right font-bold text-sm">Total Price:</td>
                            <td class="px-4 py-2 text-right font-bold text-sm" x-text="'{{ currencySymbol() }}' + subtotal.toFixed(2) + ' {{ \App\Models\GeneralSetting::first()?->currency ?? 'USD' }}'"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @error('return_items')
                <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="note" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Optional note">{{ old('note') }}</textarea>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="number" name="discount" step="0.01" min="0" x-model="discount" class="flex-1 px-3 py-2 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Restocking Fee</label>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="number" name="restocking_fee" step="0.01" min="0" x-model="restockingFee" class="flex-1 px-3 py-2 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payable to Customer</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="payableToCustomer.toFixed(2)" class="flex-1 px-3 py-2 bg-gray-50 text-sm font-semibold" />
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-lg bg-[#4634ff] text-white font-semibold hover:bg-violet-700 transition-colors">
                Submit
            </button>
        </form>
    </div>
</x-app-layout>
