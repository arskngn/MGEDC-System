<x-app-layout>
    @php
        $unitsBase = rtrim(url('/products/units'), '/');
    @endphp
    <div
        x-data="{
            addOpen: {{ $errors->any() && old('_form') === 'add' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('_form') === 'edit' ? 'true' : 'false' }},
            importOpen: {{ $errors->has('csv_file') || ($errors->any() && old('_form') === 'import') ? 'true' : 'false' }},
            editId: {{ old('unit_id') ? (int) old('unit_id') : 'null' }},
            editName: @js(old('name', '')),
            editShortName: @js(old('short_name', '')),
            openEdit(id, name, shortName) {
                this.editId = id;
                this.editName = name;
                this.editShortName = shortName;
                this.editOpen = true;
            },
        }"
        @keydown.escape.window="addOpen = false; editOpen = false; importOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Units</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="{{ route('units.index') }}" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            title="Search by unit name or short code"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('units.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(auth()->user()->hasPermission('Store Unit'))
                        <button type="button" @click="addOpen = true" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New
                        </button>
                    @endif

                    @if(auth()->user()->hasPermission('Import Unit'))
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
                <table class="w-full border-collapse min-w-[640px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Products</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($units as $unit)
                            @php
                                $sn = ($units->currentPage() - 1) * $units->perPage() + $loop->iteration;
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle">{{ $sn }}</td>
                                <td class="px-4 py-4 align-middle text-center">
                                    <div class="flex flex-col items-center justify-center gap-0.5">
                                        <span class="text-sm font-semibold text-gray-900">{{ $unit->short_name }}</span>
                                        <span class="text-xs text-gray-500">{{ $unit->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle text-center">{{ $unit->products_count }}</td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        @if(auth()->user()->hasPermission('Store Unit'))
                                            <button
                                                type="button"
                                                @click="openEdit({{ $unit->id }}, @js($unit->name), @js($unit->short_name))"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('Delete Unit'))
                                            @if($unit->products_count > 0)
                                                <span
                                                    class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-200 text-rose-300 rounded-md text-[11px] font-bold opacity-60 cursor-not-allowed select-none"
                                                    title="Cannot delete a unit that still has products assigned."
                                                    role="presentation"
                                                >
                                                    <svg class="h-3.5 w-3.5 mr-1 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </span>
                                            @else
                                                <form
                                                    method="POST"
                                                    action="{{ route('units.destroy', $unit) }}"
                                                    class="inline"
                                                    onsubmit="return confirm({{ json_encode('Delete unit '.$unit->short_name.' ('.$unit->name.')? This cannot be undone.') }});"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-500 text-rose-600 rounded-md hover:bg-rose-50 text-[11px] font-bold"
                                                    >
                                                        <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No units found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($units->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $units->links() }}
                </div>
            @endif
        </div>

        {{-- Add unit --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Unit</h3>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('units.store') }}" class="px-6 py-4 space-y-4">
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
                            <label for="add_short_name" class="block text-sm font-medium text-gray-700 mb-1">Short name <span class="text-red-500">*</span></label>
                            <input
                                id="add_short_name"
                                type="text"
                                name="short_name"
                                value="{{ old('_form') === 'add' ? old('short_name') : '' }}"
                                maxlength="32"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('short_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit unit --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Unit</h3>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" x-bind:action="`{{ $unitsBase }}/${editId}`" class="px-6 py-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit" />
                        <input type="hidden" name="unit_id" x-bind:value="editId" />
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
                            <label for="edit_short_name" class="block text-sm font-medium text-gray-700 mb-1">Short name <span class="text-red-500">*</span></label>
                            <input
                                id="edit_short_name"
                                type="text"
                                name="short_name"
                                x-model="editShortName"
                                maxlength="32"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('short_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Import CSV --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Unit</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="px-6 pt-4">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 text-[#0a1233] text-xs p-3 mb-4 space-y-1.5">
                            <p>• Format your CSV the same way as the sample file below.</p>
                            <p>• Required columns: <strong>name</strong>, <strong>short_name</strong> (max 32 characters).</p>
                            <p>• Both must be unique. <strong>short_name</strong> is used when importing products.</p>
                            <p>• When an error occurs, fix the rows in your file and import again.</p>
                            <p>• <strong>Comma</strong> or <strong>semicolon</strong> separators work (e.g. Excel “CSV UTF-8”).</p>
                        </div>

                        <form method="POST" action="{{ route('units.import') }}" enctype="multipart/form-data" class="space-y-4 pb-6">
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
                                    Download sample template file from here
                                    <a href="{{ route('units.import.sample') }}" class="font-bold text-[#5542ff] hover:underline">unit.csv</a>
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
