<x-app-layout>
    <div x-data="{
            filterBy: 'warehouse',
            selectedWarehouse: null,
            selectedProduct: null,
            warehouseSearch: '',
            productSearch: '',
            showWarehouseDropdown: false,
            showProductDropdown: false,
            showFilterByDropdown: false,
            bulkOpen: false,
            actionTop: '0px',
            actionRight: '0px',
            warehouses: {{ json_encode($warehouses->map(fn($w) => ['id' => $w->id, 'name' => $w->name])->values()) }},
            products: {{ json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'category_id' => $p->category_id, 'brand_id' => $p->brand_id])->values()) }},
            filteredWarehouses() {
                return this.warehouses.filter(w => w.name.toLowerCase().includes(this.warehouseSearch.toLowerCase()));
            },
            filteredProducts() {
                return this.products.filter(p => 
                    p.name.toLowerCase().includes(this.productSearch.toLowerCase()) || 
                    p.sku.toLowerCase().includes(this.productSearch.toLowerCase())
                );
            },
            getWarehouseName() {
                return this.warehouses.find(w => w.id === this.selectedWarehouse)?.name || 'Select One';
            },
            getProductName() {
                const product = this.products.find(p => p.id === this.selectedProduct);
                return product ? product.name + ' (' + product.sku + ')' : 'Select One';
            },
            canFilter() {
                return (this.filterBy === 'warehouse' && this.selectedWarehouse) || 
                       (this.filterBy === 'product' && this.selectedProduct);
            },
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            }
        }"
        @keydown.escape.window="bulkOpen = false"
        @scroll.window="if (bulkOpen) bulkOpen = false"
        class="space-y-4">
        <!-- Header with Title and Action Button (Hidden until filtered) -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Product Stock Report</h2>
            
            <!-- Action Dropdown - Only visible when results are shown -->
            @if($stockData->isNotEmpty())
                <div class="inline-flex">
                    <button type="button" @click.stop="toggleAction($event)" class="inline-flex items-center px-4 py-2 bg-[#48cf82] border border-[#48cf82] text-white rounded-md hover:bg-[#3dbd75] text-sm font-semibold shadow-sm">
                        Action
                        <svg class="h-4 w-4 ml-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            @if(auth()->user()->hasPermission('Download Stock Report PDF'))
                                <a href="{{ route('reports.stock.export', ['format' => 'pdf', ...request()->query()]) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download PDF
                                </a>
                            @endif
                            @if(auth()->user()->hasPermission('Download Stock Report CSV'))
                                <a href="{{ route('reports.stock.export', ['format' => 'csv', ...request()->query()]) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download CSV
                                </a>
                            @endif
                        </div>
                    </template>
                </div>
            @endif
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="GET" action="{{ route('reports.stock') }}" class="space-y-4">
                <!-- Labels Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-900">Filter By <span class="text-red-500">*</span></label>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900">
                            <span x-text="filterBy === 'warehouse' ? 'Warehouse' : 'Product'"></span> <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <div></div>
                </div>

                <!-- Controls Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <!-- Filter By Dropdown -->
                    <div class="relative">
                        <button type="button" 
                            @click="showFilterByDropdown = !showFilterByDropdown"
                            @click.outside="showFilterByDropdown = false"
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-md text-left flex justify-between items-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <span x-text="filterBy === 'warehouse' ? 'Warehouse' : 'Product'" class="text-gray-900 font-medium"></span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>
                        
                        <div x-show="showFilterByDropdown" x-cloak 
                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50">
                            <button type="button" @click="filterBy = 'warehouse'; selectedWarehouse = null; selectedProduct = null; showFilterByDropdown = false;"
                                :class="filterBy === 'warehouse' ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'"
                                class="w-full text-left px-4 py-3 text-gray-900 font-medium">
                                Warehouse
                            </button>
                            <button type="button" @click="filterBy = 'product'; selectedWarehouse = null; selectedProduct = null; showFilterByDropdown = false;"
                                :class="filterBy === 'product' ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'"
                                class="w-full text-left px-4 py-3 border-t border-gray-200 text-gray-900 font-medium">
                                Product
                            </button>
                        </div>
                    </div>

                    <!-- Warehouse Dropdown (shown when filterBy === 'warehouse') -->
                    <div x-show="filterBy === 'warehouse'" x-cloak class="relative">
                        <button type="button"
                            @click="showWarehouseDropdown = !showWarehouseDropdown"
                            @click.outside="showWarehouseDropdown = false"
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-md text-left flex justify-between items-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <span x-text="getWarehouseName()" class="text-gray-900 font-medium"></span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>

                        <div x-show="showWarehouseDropdown" x-cloak
                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50">
                            <div class="p-3 border-b border-gray-200">
                                <input type="text" x-model="warehouseSearch" placeholder="Search..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-for="warehouse in filteredWarehouses()" :key="warehouse.id">
                                    <button type="button"
                                        @click="selectedWarehouse = warehouse.id; showWarehouseDropdown = false; warehouseSearch = '';"
                                        class="w-full text-left px-4 py-3 text-gray-900 hover:bg-gray-50 border-t border-gray-100 text-sm">
                                        <span x-text="warehouse.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Product Dropdown (shown when filterBy === 'product') -->
                    <div x-show="filterBy === 'product'" x-cloak class="relative">
                        <button type="button"
                            @click="showProductDropdown = !showProductDropdown"
                            @click.outside="showProductDropdown = false"
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-md text-left flex justify-between items-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <span x-text="getProductName()" class="text-gray-900 font-medium truncate"></span>
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>

                        <div x-show="showProductDropdown" x-cloak
                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50">
                            <div class="p-3 border-b border-gray-200">
                                <input type="text" x-model="productSearch" placeholder="Search..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-for="product in filteredProducts()" :key="product.id">
                                    <button type="button"
                                        @click="selectedProduct = product.id; showProductDropdown = false; productSearch = '';"
                                        class="w-full text-left px-4 py-3 text-gray-900 hover:bg-gray-50 border-t border-gray-100 text-sm">
                                        <div class="font-medium" x-text="product.name"></div>
                                        <div class="text-xs text-gray-500" x-text="product.sku"></div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Inputs - Dynamic filter_by based on selection -->
                    <input type="hidden" name="filter_by" :value="filterBy">
                    <input type="hidden" name="warehouse_id" :value="filterBy === 'warehouse' ? selectedWarehouse : ''">
                    <input type="hidden" name="product_id" :value="filterBy === 'product' ? selectedProduct : ''">

                    <!-- Filter Button -->
                    <button type="submit" 
                        :disabled="!canFilter()"
                        :class="canFilter() ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                        class="w-full md:w-auto px-8 py-3 text-white rounded-md font-semibold transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        @if($stockData->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                                @if($filterBy === 'warehouse')
                                    <th class="px-6 py-4 text-left font-semibold">S.N</th>
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">SKU</th>
                                    <th class="px-6 py-4 text-left font-semibold">Category</th>
                                    <th class="px-6 py-4 text-left font-semibold">Brand</th>
                                    <th class="px-6 py-4 text-right font-semibold">Stock</th>
                                    <th class="px-6 py-4 text-left font-semibold">Expiration</th>
                                @else
                                    <th class="px-6 py-4 text-left font-semibold">S.N</th>
                                    <th class="px-6 py-4 text-left font-semibold">Warehouse</th>
                                    <th class="px-6 py-4 text-right font-semibold">Current Stock</th>
                                    <th class="px-6 py-4 text-left font-semibold">Expiration</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($stockData as $index => $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $index + 1 }}</td>
                                    @if($filterBy === 'warehouse')
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item['product_name'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $item['sku'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">
                                            @if($item['product_id'])
                                                @php
                                                    $product = \App\Models\Product::find($item['product_id']);
                                                    echo $product?->category?->name ?? '-';
                                                @endphp
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            @if($item['product_id'])
                                                @php
                                                    $product = \App\Models\Product::find($item['product_id']);
                                                    echo $product?->brand?->name ?? '-';
                                                @endphp
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            {{ number_format($item['stock_quantity'], 0) }} {{ $item['unit'] }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item['earliest_expiration'])
                                                @php
                                                    $status = $item['expiration_status'];
                                                    $badgeColors = [
                                                        'expired' => 'bg-red-100 text-red-800 border-red-300',
                                                        'urgent' => 'bg-orange-100 text-orange-800 border-orange-300',
                                                        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                                        'normal' => 'bg-green-100 text-green-800 border-green-300',
                                                    ];
                                                    $statusLabels = [
                                                        'expired' => 'Expired',
                                                        'urgent' => 'Urgent',
                                                        'warning' => 'Warning',
                                                        'normal' => 'Normal',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $statusLabels[$status] ?? 'Unknown' }}
                                                </span>
                                                <div class="text-xs text-gray-600 mt-1">{{ $item['earliest_expiration']->format('M d, Y') }}</div>
                                            @else
                                                <span class="text-gray-400 text-sm">No batches</span>
                                            @endif
                                        </td>
                                    @else
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item['warehouse_name'] }}</td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            {{ number_format($item['stock_quantity'], 0) }} {{ $item['unit'] }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item['earliest_expiration'])
                                                @php
                                                    $status = $item['expiration_status'];
                                                    $badgeColors = [
                                                        'expired' => 'bg-red-100 text-red-800 border-red-300',
                                                        'urgent' => 'bg-orange-100 text-orange-800 border-orange-300',
                                                        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                                        'normal' => 'bg-green-100 text-green-800 border-green-300',
                                                    ];
                                                    $statusLabels = [
                                                        'expired' => 'Expired',
                                                        'urgent' => 'Urgent',
                                                        'warning' => 'Warning',
                                                        'normal' => 'Normal',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $statusLabels[$status] ?? 'Unknown' }}
                                                </span>
                                                <div class="text-xs text-gray-600 mt-1">{{ $item['earliest_expiration']->format('M d, Y') }}</div>
                                            @else
                                                <span class="text-gray-400 text-sm">No batches</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
                <div class="text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <p class="text-gray-500 font-medium">No results found. Select a filter option and click "Filter" to view stock data.</p>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
