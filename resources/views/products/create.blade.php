<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Add Product</h2>
            <a href="{{ route('products.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('products.store') }}" method="POST" class="bg-gray-50/80 rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
                    <select name="brand_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" @selected(old('brand_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit (UoM) <span class="text-red-500">*</span></label>
                    <select name="unit_id" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>{{ $u->name }} ({{ $u->short_name }})</option>
                        @endforeach
                    </select>
                    @error('unit_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alert Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="alert_quantity" value="{{ old('alert_quantity', 0) }}" step="0.0001" min="0" required class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('alert_quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="note" rows="4" class="w-full rounded-lg border border-gray-300/80 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors resize-y min-h-[100px]" placeholder="Optional">{{ old('note') }}</textarea>
                    @error('note')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                Submit
            </button>
        </form>
    </div>
</x-app-layout>
