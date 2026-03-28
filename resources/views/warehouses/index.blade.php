<x-app-layout>
    @php
        $warehousesBase = rtrim(url('/warehouses'), '/');
        $editUsageOnLoad = 0;
        if (old('_form') === 'edit' && old('warehouse_id')) {
            $wh = \App\Models\Warehouse::withCount(['purchases', 'purchaseReturns'])->find(old('warehouse_id'));
            if ($wh) {
                $editUsageOnLoad = $wh->purchases_count + $wh->purchase_returns_count;
            }
        }
    @endphp
    <div
        x-data="{
            addOpen: {{ $errors->any() && old('_form') === 'add' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('_form') === 'edit' ? 'true' : 'false' }},
            importOpen: {{ $errors->has('csv_file') || ($errors->any() && old('_form') === 'import') ? 'true' : 'false' }},
            editId: {{ old('warehouse_id') ? (int) old('warehouse_id') : 'null' }},
            editName: @js(old('name', '')),
            editAddress: @js(old('address', '')),
            editUsage: {{ old('_form') === 'edit' ? $editUsageOnLoad : 0 }},
            openEdit(id, name, address, usage) {
                this.editId = id;
                this.editName = name;
                this.editAddress = address || '';
                this.editUsage = usage;
                this.editOpen = true;
            },
        }"
        @keydown.escape.window="addOpen = false; editOpen = false; importOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">All Warehouses</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="{{ route('warehouses.index') }}" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            title="Search by name or address"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('warehouses.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(auth()->user()->hasPermission('Store Warehouse'))
                        <button type="button" @click="addOpen = true" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            + Add New
                        </button>
                    @endif

                    @if(auth()->user()->hasPermission('Import Warehouses'))
                        <button type="button" @click="importOpen = true" class="inline-flex items-center px-4 py-2 bg-white border border-sky-400 text-sky-600 rounded-md hover:bg-sky-50 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import CSV
                        </button>
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
                <table class="w-full border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Address</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Status</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($warehouses as $warehouse)
                            @php
                                $sn = ($warehouses->currentPage() - 1) * $warehouses->perPage() + $loop->iteration;
                                $usage = $warehouse->purchases_count + $warehouse->purchase_returns_count;
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle">{{ $sn }}</td>
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle font-medium">{{ $warehouse->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600 align-middle max-w-xs">
                                    @if(filled($warehouse->address))
                                        <span class="line-clamp-2" title="{{ $warehouse->address }}">{{ $warehouse->address }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-middle text-center">
                                    @if($warehouse->status)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Enabled</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 text-[11px] font-semibold text-gray-700">Disabled</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        @if(auth()->user()->hasPermission('Store Warehouse'))
                                            <button
                                                type="button"
                                                @click="openEdit({{ $warehouse->id }}, @js($warehouse->name), @js($warehouse->address), {{ $usage }})"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('warehouses.toggle-status', $warehouse) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                @if($warehouse->status)
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-500 text-rose-600 rounded-md hover:bg-rose-50 text-[11px] font-bold"
                                                        title="Disable this warehouse for new purchases"
                                                    >
                                                        <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                        </svg>
                                                        Disable
                                                    </button>
                                                @else
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-emerald-500 text-emerald-700 rounded-md hover:bg-emerald-50 text-[11px] font-bold"
                                                        title="Enable this warehouse for new purchases"
                                                    >
                                                        <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        Enable
                                                    </button>
                                                @endif
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No warehouses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($warehouses->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $warehouses->links() }}
                </div>
            @endif
        </div>

        {{-- Add --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add Warehouse</h3>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('warehouses.store') }}" class="px-6 py-4 space-y-4">
                        @csrf
                        <input type="hidden" name="_form" value="add" />
                        <div>
                            <label for="add_name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                id="add_name"
                                type="text"
                                name="name"
                                value="{{ old('_form') === 'add' ? old('name') : '' }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="add_address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <x-address-input
                                id="add_address"
                                name="address"
                                :value="old('_form') === 'add' ? old('address') : ''"
                                placeholder="House no., street, barangay, city, province…"
                            />
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Warehouse</h3>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" x-bind:action="`{{ $warehousesBase }}/${editId}`" class="px-6 py-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit" />
                        <input type="hidden" name="warehouse_id" x-bind:value="editId" />
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                id="edit_name"
                                type="text"
                                name="name"
                                x-model="editName"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <x-address-input
                                id="edit_address"
                                name="address"
                                x-model="editAddress"
                                placeholder="House no., street, barangay, city, province…"
                            />
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Save changes</button>
                        </div>
                    </form>
                    @if(auth()->user()->hasPermission('Delete Warehouse'))
                        <form
                            method="POST"
                            x-bind:action="`{{ $warehousesBase }}/${editId}`"
                            class="px-6 pb-6 pt-0"
                            x-show="editOpen && editUsage === 0 && editId !== null"
                            x-cloak
                            @submit="return confirm('Delete this warehouse? This cannot be undone.');"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2 rounded-lg border border-rose-200 text-rose-600 text-sm font-semibold hover:bg-rose-50 transition-colors">
                                Delete warehouse
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Import CSV --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Warehouses</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="px-6 pt-4">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 text-[#0a1233] text-xs p-3 mb-4 space-y-1.5">
                            <p>• Format your CSV the same way as the sample file below.</p>
                            <p>• Required column: <strong>name</strong> (unique). Optional: <strong>address</strong>, <strong>status</strong> (1 / enabled / 0 / disabled).</p>
                            <p>• When an error occurs, fix the rows in your file and import again.</p>
                            <p>• <strong>Comma</strong> or <strong>semicolon</strong> separators work (e.g. Excel “CSV UTF-8”).</p>
                        </div>

                        <form method="POST" action="{{ route('warehouses.import') }}" enctype="multipart/form-data" class="space-y-4 pb-6">
                            @csrf
                            <input type="hidden" name="_form" value="import" />
                            <div>
                                <label for="import_csv" class="block text-sm font-medium text-gray-700 mb-1">Select File <span class="text-red-500">*</span></label>
                                <input
                                    id="import_csv"
                                    type="file"
                                    name="csv_file"
                                    accept=".csv,.txt,text/csv"
                                    required
                                    class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border file:border-gray-300 file:bg-gray-50 file:text-gray-700"
                                />
                                <p class="mt-1 text-xs text-gray-500">Supported files: <strong>csv</strong></p>
                                <p class="mt-2 text-xs text-gray-600">
                                    Download sample:
                                    <a href="{{ route('warehouses.import.sample') }}" class="font-bold text-[#5542ff] hover:underline">warehouse.csv</a>
                                </p>
                                @error('csv_file')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Import</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
