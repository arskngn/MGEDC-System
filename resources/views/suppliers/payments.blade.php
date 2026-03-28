<x-app-layout>
    <div
        x-data="{
            clearModalOpen: false
        }"
        @keydown.escape.window="clearModalOpen = false"
        class="space-y-6"
    >
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-[#0a1233]">Unsettled Payments with {{ $supplier->name }}</h2>

            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-semibold inline-flex items-center">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        @php
            $canClear = (float) $payableTotal > 0.009 || (float) $receivableTotal > 0.009;
        @endphp

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-sm text-gray-600">Payable</span>
                        <span class="font-bold text-gray-900 text-sm">{{ formatCurrency($payableTotal) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-sm text-gray-600">Receivable</span>
                        <span class="font-bold text-gray-900 text-sm">{{ formatCurrency($receivableTotal) }}</span>
                    </div>
                </div>

                @if(auth()->user()->hasPermission('Supplier Payment Clear'))
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-[#48cf82] border border-[#48cf82] text-white font-semibold hover:bg-[#3dbd75] transition-colors shadow-sm disabled:opacity-50 disabled:pointer-events-none {{ $canClear ? '' : 'opacity-50 cursor-not-allowed pointer-events-none' }}"
                        :disabled="! {{ $canClear ? 'true' : 'false' }}"
                        @click="clearModalOpen = true"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Clear Payment
                    </button>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">Invoice No.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">Reason</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($lines as $i => $line)
                            @php
                                $isPayable = $line['reason'] === 'Purchase';
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">{{ $i + 1 }}</td>
                                <td class="px-4 py-4 align-middle text-sm font-bold text-[#2563eb]">{{ $line['invoice_no'] }}</td>
                                <td class="px-4 py-4 align-middle text-sm font-medium text-gray-700">{{ $line['reason'] }}</td>
                                <td class="px-4 py-4 align-middle text-sm font-bold text-right {{ $isPayable ? 'text-red-600' : 'text-green-600' }}">
                                    {{ formatCurrency($line['amount']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No unsettled payments for this supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Clear payment modal --}}
        <div x-show="clearModalOpen" x-cloak class="fixed inset-0 z-[260] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50 z-0" @click="clearModalOpen = false"></div>
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex items-start justify-between px-6 pt-5 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Clear Full Payment with {{ $supplier->name }}</h3>
                        </div>
                        <button type="button" @click="clearModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Payable Amount</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency($payableTotal) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Receivable Amount</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency($receivableTotal) }}</span>
                            </div>
                        </div>

                        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-950 px-4 py-3">
                            <div class="text-[12px] font-semibold mb-1">Warning</div>
                            <div class="text-[12px] text-amber-950/90 leading-relaxed">
                                <span class="italic">If you click on “Clear Payment” button, all payable and receivable dues will be cleared. So be sure before click.</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('suppliers.payments.clear', $supplier) }}" class="flex gap-3">
                            @csrf
                            <button
                                type="button"
                                @click="clearModalOpen = false"
                                class="flex-1 py-2 rounded-lg border border-gray-200 text-gray-800 font-semibold hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex-1 py-2 rounded-lg bg-[#3b2ad6] text-white font-semibold hover:bg-violet-700 transition-colors disabled:opacity-50 disabled:pointer-events-none"
                                {{ $canClear ? '' : 'disabled' }}
                            >
                                Clear Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

