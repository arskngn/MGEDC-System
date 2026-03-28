<x-app-layout>
    <div class="space-y-6">
        <h2 class="text-xl font-bold text-gray-800">Notifications</h2>

        @if(isset($logs) && $logs->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
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
                            @foreach($logs as $log)
                                <tr class="bg-white hover:bg-gray-50/90 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-semibold text-[#0a1233]">
                                            {{ $log->customer?->name ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ $log->channel ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ $log->user?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ $log->subject ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <button type="button"
                                                class="px-3 py-1.5 border border-gray-200 rounded-md text-gray-700 hover:bg-gray-50"
                                                onclick="alert({{ json_encode($log->message ?? '') }})">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            </div>
        @else
            <div class="bg-white p-12 rounded-xl shadow-sm border border-gray-200 flex flex-col items-center justify-center text-center">
                <div class="text-gray-300 mb-4">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                    </svg>
                </div>
                <p class="text-gray-500">No notification found.</p>
            </div>
        @endif
    </div>
</x-app-layout>
