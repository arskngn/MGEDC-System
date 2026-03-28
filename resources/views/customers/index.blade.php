<x-app-layout>
    <div
        x-data="{
            bulkOpen: false,
            importOpen: {{ $errors->has('csv_file') ? 'true' : 'false' }},
            addOpen: false,
            editOpen: false,
            actionTop: '0px',
            actionRight: '0px',
            editActionUrlBase: '{{ route('customers.update', '__CUSTOMER__') }}',
            editActionUrl: '',
            editCustomer: {
                id: null,
                name: '',
                email: '',
                mobile: '',
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
            openEditCustomer(c) {
                this.editCustomer = {
                    id: c.id,
                    name: c.name ?? '',
                    email: c.email ?? '',
                    mobile: c.phone ?? '',
                    address: c.address ?? '',
                };
                this.editActionUrl = this.editActionUrlBase.replace('__CUSTOMER__', String(c.id));
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
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Customers</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="{{ route('customers.index') }}" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            title="Search by customer name, phone, email, or address"
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                        />
                        <button type="submit" class="px-3 bg-[#5542ff] text-white rounded-r-md hover:bg-[#4736d6] transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                    @if(request()->filled('search'))
                        <a href="{{ route('customers.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(auth()->user()->hasPermission('Store Customer'))
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

                    @if(auth()->user()->hasPermission('Customer Notification Send To All'))
                        <a href="{{ route('customers.notifications.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-md hover:bg-gray-50 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9" />
                            </svg>
                            Notification to All
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('Download Customer PDF') || auth()->user()->hasPermission('Download Customer CSV') || auth()->user()->hasPermission('Import Customers'))
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
                                    @if(auth()->user()->hasPermission('Download Customer PDF'))
                                        <a href="{{ route('customers.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                            Download PDF
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('Download Customer CSV'))
                                        <a href="{{ route('customers.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Download CSV
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('Import Customers'))
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
                <table class="w-full text-center border-collapse min-w-[980px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name | Address</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Mobile | Email</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Receivable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Payable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($customers as $i => $customer)
                            @php
                                $receivable = (float) ($customer->receivable_total ?? 0);
                                $payable = (float) ($customer->payable_total ?? 0);
                                // The payment button should be enabled if there is either a receivable OR a payable balance to settle.
                                $paymentDisabled = $receivable <= 0.009 && $payable <= 0.009;
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">{{ $i + 1 }}</td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-start leading-snug">
                                        <div class="text-sm font-bold text-[#5542ff]">{{ $customer->name }}</div>
                                        @if(filled($customer->address))
                                            <div class="text-[12px] text-gray-500 line-clamp-2 max-w-[260px] text-left" title="{{ $customer->address }}">{{ $customer->address }}</div>
                                        @else
                                            <div class="text-[12px] text-gray-400">—</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-start leading-snug">
                                        <span class="text-sm font-medium text-gray-600">{{ $customer->phone ?? '—' }}</span>
                                        <span class="text-[12px] text-gray-500">{{ $customer->email ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold {{ $receivable > 0.009 ? 'text-green-600' : 'text-gray-600' }}">
                                    {{ formatCurrency($receivable) }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold {{ $payable > 0.009 ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ formatCurrency($payable) }}
                                </td>
                                <td class="px-4 py-4 align-middle text-right">
                                    <div class="flex justify-end gap-2 items-center flex-nowrap">
                                        @if(auth()->user()->hasPermission('Store Customer'))
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold"
                                                @click="openEditCustomer({ id: {{ $customer->id }}, name: @js($customer->name), email: @js($customer->email), phone: @js($customer->phone), address: @js($customer->address) })"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('Customer Notification Single'))
                                            <a
                                                href="{{ route('customers.notifications.single', $customer) }}"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-orange-200 text-orange-600 rounded-md text-[11px] font-bold hover:bg-orange-50 transition-colors"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.5 10.5a3 3 0 01-3 3H9a3 3 0 00-3 3v0m8.5-8.5a2.5 2.5 0 00-5 0v0a2.5 2.5 0 005 0z" />
                                                </svg>
                                                Notify
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('All Customer Payments'))
                                            <a
                                                href="{{ route('customers.payments.index', $customer) }}"
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
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>

        {{-- Add modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[220] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Customer</h3>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile" value="{{ old('mobile') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <x-address-input
                                id="add_address_customer"
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
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Customer</h3>
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
                                <input type="text" name="name" x-model="editCustomer.name" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="editCustomer.email" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile" x-model="editCustomer.mobile" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <x-address-input
                                id="edit_address_customer"
                                name="address"
                                x-model="editCustomer.address"
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

