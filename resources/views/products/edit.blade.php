<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Edit Product</h2>
            <a href="{{ route('products.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('products.update', $product) }}" method="POST" class="bg-gray-50/80 rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
                    <select name="brand_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit (UoM) <span class="text-red-500">*</span></label>
                    <select name="unit_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id', $product->unit_id) == $u->id)>{{ $u->name }} ({{ $u->short_name }})</option>
                        @endforeach
                    </select>
                    @error('unit_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alert Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="alert_quantity" value="{{ old('alert_quantity', $product->alert_quantity) }}" step="0.0001" min="0" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('alert_quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="note" rows="4" class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors resize-y min-h-[100px]" placeholder="Optional">{{ old('note', $product->note) }}</textarea>
                    @error('note')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                Submit
            </button>
        </form>

        <!-- Product Batches Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Add New Batch</h3>
                <form action="{{ route('products.batches.store', $product) }}" method="POST" class="bg-gray-50/80 rounded-lg border border-gray-200 p-4 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number <span class="text-red-500">*</span></label>
                            <input type="text" name="batch_number" value="{{ old('batch_number') }}" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                            @error('batch_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse <span class="text-red-500">*</span></label>
                            <select name="warehouse_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                                <option value="">Select One</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>{{ $w->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" value="{{ old('quantity') }}" step="0.0001" min="0" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                            @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date <span class="text-red-500">*</span></label>
                            <input type="date" name="expiration_date" value="{{ old('expiration_date') }}" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                            @error('expiration_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Manufactured Date</label>
                            <input type="date" name="manufactured_date" value="{{ old('manufactured_date') }}" class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                            @error('manufactured_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full py-2 px-4 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                                Add Batch
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Existing Batches</h3>
                
                @if($batches->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                        <p class="text-gray-500">No batches found for this product.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Batch Number</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Warehouse</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-900">Quantity</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Expiration Date</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Status</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($batches as $batch)
                                    @php
                                        $now = now();
                                        $expirationDate = $batch->expiration_date;
                                        $daysLeft = $now->diffInDays($expirationDate, false);
                                        
                                        if ($daysLeft < 0) {
                                            $status = ['type' => 'expired', 'label' => 'Expired'];
                                        } elseif ($daysLeft < 5) {
                                            $status = ['type' => 'urgent', 'label' => "Expires in $daysLeft days"];
                                        } elseif ($daysLeft < 30) {
                                            $status = ['type' => 'warning', 'label' => "Expires in $daysLeft days"];
                                        } else {
                                            $status = ['type' => 'normal', 'label' => "Expires in $daysLeft days"];
                                        }
                                        
                                        $badgeClasses = [
                                            'expired' => 'bg-red-100 text-red-800 border border-red-300',
                                            'urgent' => 'bg-orange-100 text-orange-800 border border-orange-300',
                                            'warning' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                            'normal' => 'bg-green-100 text-green-800 border border-green-300',
                                        ];
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $batch->batch_number }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $batch->warehouse->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                            {{ number_format($batch->quantity, 4) }} {{ $product->unit?->short_name ?? 'Unit' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ $expirationDate->format('M d, Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold {{ $badgeClasses[$status['type']] }}">
                                                {{ $status['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" onclick="editBatch({{ $batch->id }}, '{{ $batch->batch_number }}', {{ $batch->warehouse_id }}, {{ $batch->quantity }}, '{{ $batch->expiration_date->format('Y-m-d') }}', '{{ $batch->manufactured_date?->format('Y-m-d') ?? '' }}')" class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                                    Edit
                                                </button>
                                                <form action="{{ route('products.batches.delete', [$product, $batch]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this batch?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-red-100 text-red-800 hover:bg-red-200 transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Edit Batch Modal -->
        <div id="editBatchModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Edit Batch</h2>
                <form id="editBatchForm" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number <span class="text-red-500">*</span></label>
                        <input type="text" id="editBatchNumber" name="batch_number" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse <span class="text-red-500">*</span></label>
                        <select id="editWarehouseId" name="warehouse_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                            <option value="">Select One</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" id="editQuantity" name="quantity" step="0.0001" min="0" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date <span class="text-red-500">*</span></label>
                        <input type="date" id="editExpirationDate" name="expiration_date" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Manufactured Date</label>
                        <input type="date" id="editManufacturedDate" name="manufactured_date" class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    </div>
                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="flex-1 py-2 px-4 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                            Update
                        </button>
                        <button type="button" onclick="closeEditBatchModal()" class="flex-1 py-2 px-4 rounded-lg bg-gray-200 text-gray-900 font-semibold hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>

    <script>
        let currentBatchId = null;

        function editBatch(batchId, batchNumber, warehouseId, quantity, expirationDate, manufacturedDate) {
            currentBatchId = batchId;
            document.getElementById('editBatchNumber').value = batchNumber;
            document.getElementById('editWarehouseId').value = warehouseId;
            document.getElementById('editQuantity').value = quantity;
            document.getElementById('editExpirationDate').value = expirationDate;
            document.getElementById('editManufacturedDate').value = manufacturedDate;
            
            const form = document.getElementById('editBatchForm');
            form.action = `/products/{{ $product->id }}/batches/${batchId}`;
            
            document.getElementById('editBatchModal').classList.remove('hidden');
        }

        function closeEditBatchModal() {
            document.getElementById('editBatchModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('editBatchModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditBatchModal();
            }
        });
    </script>
</x-app-layout>
