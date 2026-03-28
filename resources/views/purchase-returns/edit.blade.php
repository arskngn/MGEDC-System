<x-app-layout>
    <script type="application/json" id="purchase-return-edit-lines">@json($lines)</script>
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            items: [],
            discount: {{ old('discount', $purchaseReturn->discount) }},
            init() {
                const el = document.getElementById('purchase-return-edit-lines');
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
            get receivable() { return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0)); },
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Edit Purchase Return</h2>
            <a href="{{ route('purchases.return') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('purchases.return.update', $purchaseReturn) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice No. <span class="text-red-500">*</span></label>
                    <input type="text" value="{{ $purchaseReturn->return_invoice_no }}" disabled class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                    <input type="text" value="{{ $purchaseReturn->purchase->supplier->name }}" disabled class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="return_date" value="{{ old('return_date', $purchaseReturn->return_date->format('Y-m-d')) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#5542ff]" />
                    @error('return_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse <span class="text-red-500">*</span></label>
                    <input type="text" value="{{ $purchaseReturn->warehouse->name }}" disabled class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#0a1233] text-white">
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Purchase Qty</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Stock Qty</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Return Qty <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Price</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-4 py-2">
                                    <input type="hidden" :name="'items['+index+'][purchase_item_id]'" :value="item.purchase_item_id" />
                                    <span class="text-gray-700 bg-gray-50 rounded px-2 py-1 block" x-text="item.product_name"></span>
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    <span x-text="parseFloat(item.purchase_quantity).toString()"></span>
                                    <span class="text-xs text-gray-500 ml-1" x-text="item.unit_label"></span>
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    <span x-text="parseFloat(item.stock_quantity).toString()"></span>
                                    <span class="text-xs text-gray-500 ml-1" x-text="item.unit_label"></span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-0 rounded border border-gray-200 overflow-hidden max-w-[140px]">
                                        <input
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            :max="item.max_return"
                                            :name="'items['+index+'][return_quantity]'"
                                            x-model.number="item.return_quantity"
                                            class="flex-1 min-w-0 px-2 py-1.5 text-sm border-0 focus:ring-0"
                                        />
                                        <span class="px-2 py-1.5 bg-gray-100 text-gray-600 text-xs shrink-0" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <span class="text-sm text-gray-800">{{ currencySymbol() }}<span x-text="Number(item.unit_price).toFixed(2)"></span> {{ $currencyCode }}</span>
                                </td>
                                <td class="px-4 py-2 font-medium text-gray-900">
                                    <span class="text-sm">{{ currencySymbol() }}<span x-text="rowTotal(index).toFixed(2)"></span> {{ $currencyCode }}</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p class="text-right text-sm text-gray-600">
                Total Price: <span class="font-bold text-[#0a1233]">{{ currencySymbol() }}<span x-text="subtotal.toFixed(2)"></span> {{ $currencyCode }}</span>
            </p>
            @error('items')<p class="text-red-500 text-sm">{{ $message }}</p>@enderror

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Return Note</label>
                    <textarea name="note" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#5542ff]" placeholder="Optional note">{{ old('note', $purchaseReturn->note) }}</textarea>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                            <input type="number" name="discount" step="0.01" min="0" x-model="discount" class="flex-1 px-3 py-2 text-sm focus:ring-1 focus:ring-[#5542ff]" />
                        </div>
                        @error('discount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Receivable Amount</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-100 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="receivable.toFixed(2)" class="flex-1 px-3 py-2 bg-gray-100 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Received Amount</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-100 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                            <input type="text" value="{{ number_format((float) $purchaseReturn->received_amount, 2, '.', '') }}" readonly class="flex-1 px-3 py-2 bg-gray-100 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Amount</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-100 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="Math.max(0, receivable - {{ (float) $purchaseReturn->received_amount }}).toFixed(2)" class="flex-1 px-3 py-2 bg-gray-100 text-sm" />
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                Submit
            </button>
        </form>
    </div>
</x-app-layout>
