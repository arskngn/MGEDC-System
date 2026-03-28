<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-[#0a1233]">
                Notifications Sent to {{ $customer->name }}
            </h2>

            <a href="{{ route('customers.notifications.single.send-form', $customer) }}"
               class="inline-flex items-center px-4 py-2 bg-[#5542ff] text-white rounded-md hover:bg-[#4634ff] transition-colors text-sm font-semibold">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"/>
                </svg>
                Send Notification
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[980px]">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">User</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">Sent</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">Sender</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">Subject</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="bg-white hover:bg-gray-50/90 transition-colors">
                                <td class="px-4 py-4 align-middle text-sm font-bold text-[#5542ff]">
                                    {{ $customer->name }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    {{ optional($log->created_at)->format('Y-m-d H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    {{ $log->user?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    {{ $log->subject ?? '—' }}
                                </td>
                                <td class="px-4 py-4 align-middle text-sm">
                                    <span class="text-gray-400">—</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Data not found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

