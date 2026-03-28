<x-app-layout>
    <div
        x-data="{
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
            addOpen: false,
            editOpen: false,
            importOpen: false,
            editId: null,
            toggleMore(id, $event) {
                if (this.openMoreId === id) {
                    this.openMoreId = null;
                    return;
                }
                const r = $event.currentTarget.getBoundingClientRect();
                this.dropdownTop = (r.bottom + 4) + 'px';
                this.dropdownRight = (window.innerWidth - r.right) + 'px';
                this.openMoreId = id;
            },
            openEdit(id) {
                this.editId = id;
                this.editOpen = true;
                this.openMoreId = null;
            },
        }"
        @keydown.escape.window="openMoreId = null; addOpen = false; editOpen = false; importOpen = false"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">All Expenses</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form
                    method="GET"
                    action="{{ route('expenses.index') }}"
                    x-data="purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) })"
                    x-ref="filterForm"
                    @keydown.escape.window="escapeClose()"
                    class="inline-flex flex-wrap items-center gap-2"
                >
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    <div class="relative flex w-full sm:w-[260px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    @include('components.date-range-picker')

                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                        <a href="{{ route('expenses.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(auth()->user()->hasPermission('Store Expense'))
                        <button type="button" @click="addOpen = true" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New
                        </button>
                    @endif

                    @if(auth()->user()->hasPermission('Download Expense PDF') || auth()->user()->hasPermission('Download Expense CSV') || auth()->user()->hasPermission('Import Expenses'))
                        <div class="relative">
                            <button type="button" @click="openMoreId === 'action' ? openMoreId = null : openMoreId = 'action'" class="inline-flex items-center px-4 py-2 bg-[#48cf82] border border-[#48cf82] text-white rounded-md hover:bg-[#3dbd75] text-sm font-semibold shadow-sm">
                                Action
                                <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div
                                x-show="openMoreId === 'action'"
                                @click.outside="openMoreId = null"
                                x-cloak
                                class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-[200] py-1"
                            >
                                @if(auth()->user()->hasPermission('Download Expense PDF'))
                                    <a href="{{ route('expenses.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        Download PDF
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('Download Expense CSV'))
                                    <a href="{{ route('expenses.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Download CSV
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('Import Expenses'))
                                    <button type="button" @click="importOpen = true; openMoreId = null" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Import CSV
                                    </button>
                                @endif
                            </div>
                        </div>
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
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Reason</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Date</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Amount</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Note</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($expenses as $expense)
                            @php
                                $sn = ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration;
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle">{{ $sn }}</td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm font-semibold text-gray-900">{{ $expense->expenseType->name }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm text-gray-700">{{ $expense->date->format('d M, Y') }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm font-semibold text-gray-900">{{ formatCurrency($expense->amount) }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm text-gray-700">{{ $expense->description ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(auth()->user()->hasPermission('Store Expense'))
                                            <button
                                                type="button"
                                                @click="openEdit({{ $expense->id }})"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold transition-colors"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>

        {{-- Add expense modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-10" @click.stop>
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Add New Expense</h3>
                    </div>
                    <form method="POST" action="{{ route('expenses.store') }}" class="px-6 py-6 space-y-4">
                        @csrf
                        <div>
                            <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select
                                id="expense_type_id"
                                name="expense_type_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            >
                                <option value="">Select One</option>
                                @foreach($expenseTypes ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date of Expense <span class="text-red-500">*</span></label>
                            <input
                                id="date"
                                type="date"
                                name="date"
                                value="{{ now()->format('Y-m-d') }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600 font-medium">{{ currencySymbol() }}</span>
                                <input
                                    id="amount"
                                    type="number"
                                    name="amount"
                                    step="0.01"
                                    min="0"
                                    placeholder="500"
                                    required
                                    class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="2"
                                placeholder="Utility for Office !"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm resize-none"
                            ></textarea>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="addOpen = false"
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#5542ff] text-white text-sm font-medium hover:bg-[#4736d6] transition-colors">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit expense modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-10" @click.stop>
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Edit Expense Info</h3>
                    </div>
                    <form id="editExpenseForm" :action="`/expenses/${editId}`" method="POST" class="px-6 py-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="expense_type_id_edit" class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select
                                id="expense_type_id_edit"
                                name="expense_type_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            >
                                <option value="">Select One</option>
                                @foreach($expenseTypes ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_edit" class="block text-sm font-medium text-gray-700 mb-1">Date of Expense <span class="text-red-500">*</span></label>
                            <input
                                id="date_edit"
                                type="date"
                                name="date"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                        </div>

                        <div>
                            <label for="amount_edit" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600 font-medium">{{ currencySymbol() }}</span>
                                <input
                                    id="amount_edit"
                                    type="number"
                                    name="amount"
                                    step="0.01"
                                    min="0"
                                    placeholder="500"
                                    required
                                    class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="description_edit" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                            <textarea
                                id="description_edit"
                                name="description"
                                rows="2"
                                placeholder="Utility for Office !"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm resize-none"
                            ></textarea>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="editOpen = false"
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#5542ff] text-white text-sm font-medium hover:bg-[#4736d6] transition-colors">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        {{-- Import expense modal --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Expense</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('expenses.import') }}" enctype="multipart/form-data" class="px-6 py-4 space-y-4">
                        @csrf
                        <input type="hidden" name="_form" value="import" />
                        
                        <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800 text-sm space-y-1.5">
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Format your CSV the same way as the sample file below.</li>
                                <li>Valid fields: <em>expense_type, date_of_expense, amount, note</em></li>
                                <li>All fields are required except note.</li>
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
                            <a href="{{ route('expenses.import.sample') }}" class="inline-flex items-center text-[#5542ff] hover:underline font-medium">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                expense.csv
                            </a>
                        </div>

                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>
        // Load expense data when edit modal opens
        document.addEventListener('alpine:init', () => {
            const root = document.querySelector('[x-data*="openMoreId"]');
            if (root) {
                const alpineInstance = root.__x_instance;
                
                // Watch for editOpen changes
                const originalOpenEdit = alpineInstance.openEdit;
                alpineInstance.openEdit = function(id) {
                    originalOpenEdit.call(this, id);
                    
                    // Fetch expense data
                    fetch(`/expenses/${id}/edit`)
                        .then(response => response.json())
                        .then(data => {
                            document.querySelector('#expense_type_id_edit').value = data.expense_type_id;
                            document.querySelector('#date_edit').value = data.date;
                            document.querySelector('#amount_edit').value = data.amount;
                            document.querySelector('#description_edit').value = data.description || '';
                            
                            // Update form action
                            document.querySelector('#editExpenseForm').action = `/expenses/${id}`;
                        });
                };
            }
        });
    </script>
</x-app-layout>
