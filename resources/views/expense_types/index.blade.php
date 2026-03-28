<x-app-layout>
    @php
        $expenseTypesBase = rtrim(url('/expense_types'), '/');
    @endphp
    <div
        x-data="{
            addOpen: {{ $errors->any() && old('_form') === 'add' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('_form') === 'edit' ? 'true' : 'false' }},
            importOpen: {{ $errors->has('csv_file') || ($errors->any() && old('_form') === 'import') ? 'true' : 'false' }},
            deleteOpen: false,
            deleteId: null,
            deleteName: '',
            editId: {{ old('expense_type_id') ? (int) old('expense_type_id') : 'null' }},
            editName: @js(old('name', '')),
            openEdit(id, name) {
                this.editId = id;
                this.editName = name;
                this.editOpen = true;
            },
            openDelete(id, name) {
                this.deleteId = id;
                this.deleteName = name;
                this.deleteOpen = true;
            },
            confirmDelete() {
                const form = document.getElementById(`delete-form-${this.deleteId}`);
                if (form) {
                    form.submit();
                }
                this.deleteOpen = false;
            }
        }"
        @keydown.escape.window="addOpen = false; editOpen = false; importOpen = false; deleteOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Expense Types</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="{{ route('expense_types.index') }}" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            title="Search by expense type name"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('expense_types.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(auth()->user()->hasPermission('Store Expense Type'))
                        <button type="button" @click="addOpen = true" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New
                        </button>
                    @endif

                    @if(auth()->user()->hasPermission('Import Expense Types'))
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
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($expenseTypes as $expenseType)
                            @php
                                $sn = ($expenseTypes->currentPage() - 1) * $expenseTypes->perPage() + $loop->iteration;
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle">{{ $sn }}</td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm font-semibold text-gray-900">{{ $expenseType->name }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(auth()->user()->hasPermission('Store Expense Type'))
                                            <button
                                                type="button"
                                                @click="openEdit({{ $expenseType->id }}, @js($expenseType->name))"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold transition-colors"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('Delete Expense Type'))
                                            @if($expenseType->expenses_count > 0)
                                                <span
                                                    class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-300 text-rose-400 rounded-md text-[11px] font-bold opacity-50 cursor-not-allowed select-none"
                                                    title="Cannot delete an expense type that still has expenses assigned."
                                                    role="presentation"
                                                >
                                                    <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </span>
                                            @else
                                                <form
                                                    id="delete-form-{{ $expenseType->id }}"
                                                    method="POST"
                                                    action="{{ route('expense_types.destroy', $expenseType) }}"
                                                    class="inline"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="button"
                                                        @click="openDelete({{ $expenseType->id }}, @js($expenseType->name))"
                                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-500 text-rose-600 rounded-md hover:bg-rose-50 text-[11px] font-bold transition-colors"
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
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">No expense types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenseTypes->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $expenseTypes->links() }}
                </div>
            @endif
        </div>

        {{-- Add expense type modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Expense Type</h3>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('expense_types.store') }}" class="px-6 py-4 space-y-4">
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
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit expense type modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Expense Type</h3>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" x-bind:action="`{{ $expenseTypesBase }}/${editId}`" class="px-6 py-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit" />
                        <input type="hidden" name="expense_type_id" x-bind:value="editId" />
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
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Import expense types modal --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Expense Type</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('expense_types.import') }}" enctype="multipart/form-data" class="px-6 py-4 space-y-4">
                        @csrf
                        <input type="hidden" name="_form" value="import" />
                        
                        <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800 text-sm space-y-1.5">
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Format your CSV the same way as the sample file below.</li>
                                <li>Valid fields: <em>name</em></li>
                                <li>Name field must be unique and is required.</li>
                                <li>When an error occurs download the error file and correct the incorrect cells and import that file again through format.</li>
                            </ul>
                        </div>

                        <div>
                            <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Select File <span class="text-red-500">*</span></label>
                            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-[#5542ff] transition-colors">
                                <input
                                    id="csv_file"
                                    type="file"
                                    name="csv_file"
                                    accept=".csv"
                                    required
                                    class="hidden"
                                    onchange="document.querySelector('[data-file-name]').textContent = this.files?.[0]?.name || 'No file chosen';"
                                />
                                <label for="csv_file" class="cursor-pointer flex flex-col items-center gap-2">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div class="text-sm">
                                        <span class="font-medium text-[#5542ff]">Click to choose file</span>
                                        <span class="text-gray-600"> or drag and drop</span>
                                    </div>
                                    <span class="text-xs text-gray-500">CSV format</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500" data-file-name>No file chosen</p>
                            @error('csv_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="text-sm text-gray-600">
                            <p class="mb-2">Download sample template:</p>
                            <a href="{{ route('expense_types.import.sample') }}" class="inline-flex items-center text-[#5542ff] hover:underline font-medium">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                expense_type.csv
                            </a>
                        </div>

                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Delete confirmation modal --}}
        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="deleteOpen = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full z-10" @click.stop>
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-base font-semibold text-gray-900">Confirmation Alert!</h3>
                    </div>
                    <div class="px-6 pb-6">
                        <p class="text-sm text-gray-600">Are you sure to delete this expense type?</p>
                    </div>
                    <div class="px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
                        <button 
                            type="button" 
                            @click="deleteOpen = false" 
                            class="px-6 py-2 rounded-sm bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition-colors"
                        >
                            No
                        </button>
                        <button 
                            type="button" 
                            @click="confirmDelete()" 
                            class="px-6 py-2 rounded-sm bg-[#5542ff] text-white text-sm font-medium hover:bg-[#4736d6] transition-colors"
                        >
                            Yes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
