<x-app-layout>
    <div
        x-data="{
            bulkOpen: false,
            importOpen: {{ $errors->has('csv_file') ? 'true' : 'false' }},
            addOpen: false,
            editOpen: false,
            actionTop: '0px',
            actionRight: '0px',
            editActionUrlBase: '{{ route('suppliers.update', '__SUPPLIER__') }}',
            editActionUrl: '',
            editSupplier: {
                id: null,
                name: '',
                email: '',
                mobile: '',
                company_name: '',
                address: '',
            },
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            },
            openEditSupplier(s) {
                this.editSupplier = {
                    id: s.id,
                    name: s.name ?? '',
                    email: s.email ?? '',
                    mobile: s.phone ?? '',
                    company_name: s.company_name ?? '',
                    address: s.address ?? '',
                };
                this.editActionUrl = this.editActionUrlBase.replace('__SUPPLIER__', String(s.id));
                this.editOpen = true;
                this.addOpen = false;
                this.bulkOpen = false;
            }
        }"
        @keydown.escape.window="importOpen = false; addOpen = false; editOpen = false; bulkOpen = false"
        @scroll.window="if (bulkOpen) bulkOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Suppliers</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="{{ route('suppliers.index') }}" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            title="Search by supplier name, phone, email, or company"
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(auth()->user()->hasPermission('Store Supplier'))
                        <button
                            type="button"
                            @click="addOpen = true; editOpen = false; bulkOpen = false"
                            class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold"
                        >
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            + Add New
                        </button>
                    @endif

                    @if(auth()->user()->hasPermission('Download Supplier PDF') || auth()->user()->hasPermission('Download Supplier CSV') || auth()->user()->hasPermission('Import Suppliers'))
                        <div class="inline-flex">
                            <button type="button" @click.stop="toggleAction($event)" class="inline-flex items-center px-4 py-2 bg-[#48cf82] border border-[#48cf82] text-white rounded-md hover:bg-[#3dbd75] text-sm font-semibold shadow-sm">
                                Action
                                <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                    @if(auth()->user()->hasPermission('Download Supplier PDF'))
                                        <a href="{{ route('suppliers.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                            Download PDF
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('Download Supplier CSV'))
                                        <a href="{{ route('suppliers.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Download CSV
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('Import Suppliers'))
                                        <button type="button" @click="importOpen = true; bulkOpen = false" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                            Import CSV
                                        </button>
                                    @endif
                                </div>
                            </template>
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
        @if($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <div class="font-semibold mb-1">Please fix the following:</div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Mobile | Email</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Payable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Receivable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($suppliers as $i => $supplier)
                            @php
                                $payable = (float) ($supplier->payable_total ?? 0);
                                $receivable = (float) ($supplier->receivable_total ?? 0);
                                // The payment button should be enabled if there is either a payable OR a receivable balance to settle.
                                $paymentDisabled = $payable <= 0.009 && $receivable <= 0.009;
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">{{ $i + 1 }}</td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="text-sm font-bold text-[#5542ff] leading-snug">{{ $supplier->name }}</div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-medium text-gray-600">
                                    <div class="flex flex-col items-start leading-snug">
                                        <span>{{ $supplier->phone ?? '—' }}</span>
                                        <span class="text-[12px] text-gray-500">{{ $supplier->email ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold {{ $payable > 0.009 ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ formatCurrency($payable) }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold {{ $receivable > 0.009 ? 'text-green-600' : 'text-gray-600' }}">
                                    {{ formatCurrency($receivable) }}
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex justify-center gap-2 items-center">
                                        @if(auth()->user()->hasPermission('Store Supplier'))
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold"
                                                @click="openEditSupplier({ id: {{ $supplier->id }}, name: @js($supplier->name), email: @js($supplier->email), phone: @js($supplier->phone), company_name: @js($supplier->company_name), address: @js($supplier->address) })"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('Supplier Payment Index'))
                                            <a
                                                href="{{ route('suppliers.payments.index', $supplier) }}"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#2563eb] text-[#2563eb] rounded-md text-[11px] font-bold hover:bg-[#2563eb]/5 {{ $paymentDisabled ? 'pointer-events-none opacity-50 cursor-not-allowed' : '' }}"
                                                {{ $paymentDisabled ? 'tabindex=-1 aria-disabled=true' : '' }}
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2h2m8-6l-4 4m0 0l-4-4m4 4V9" />
                                                </svg>
                                                Payment
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No suppliers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>

        {{-- Import modal --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Supplier</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-950 text-xs p-3 mb-4 space-y-1">
                        <p class="font-semibold">CSV import requirements:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Columns must be: <strong>name</strong>, <strong>email</strong>, <strong>mobile</strong>, <strong>company_name</strong>, <strong>address</strong>.</li>
                            <li>Required columns: <strong>name</strong>, <strong>email</strong>, <strong>mobile</strong>.</li>
                            <li>Cells for required fields must not be empty.</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('suppliers.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select File <span class="text-red-500">*</span></label>
                            <input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-[#5542ff]/10 file:text-[#5542ff] file:font-semibold" />
                            @error('csv_file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="text-sm text-gray-600">
                            Download sample template:
                            <a href="{{ route('suppliers.import.sample') }}" class="text-[#5542ff] font-semibold hover:underline">supplier.csv</a>
                        </p>

                        <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                            Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[220] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>

                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Supplier</h3>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mobile <span class="text-red-500">*</span></label>
                                <input type="text" name="mobile" value="{{ old('mobile') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                                @error('mobile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                                @error('company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <x-address-input
                                    id="add_address"
                                    name="address"
                                    :value="old('address', '')"
                                    placeholder="House no., street, barangay, city, province…"
                                />
                        </div>

                        <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[230] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>

                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Supplier</h3>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form method="POST" :action="editActionUrl" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="editSupplier.name" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="editSupplier.email" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mobile <span class="text-red-500">*</span></label>
                                <input type="text" name="mobile" x-model="editSupplier.mobile" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                <input type="text" name="company_name" x-model="editSupplier.company_name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <x-address-input
                                id="edit_address"
                                name="address"
                                x-model="editSupplier.address"
                                placeholder="House no., street, barangay, city, province…"
                            />
                        </div>

                        <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

