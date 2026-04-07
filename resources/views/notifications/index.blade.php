<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Notifications Center</h2>
                <p class="text-sm text-gray-500 mt-1">Manage your system alerts and customer communications.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button"
                        onclick="markAllNotificationsAsRead()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark All as Read
                </button>
                <button type="button"
                        onclick="deleteAllNotifications()"
                        class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 text-sm font-bold rounded-lg transition-all flex items-center border border-red-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete All
                </button>
            </div>
        </div>

        <!-- System Alerts (Expiration, Stock, etc.) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    System Alerts
                </h3>
                <span class="bg-blue-100 text-blue-600 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $notifications->total() }} total</span>
            </div>
            
            <div class="divide-y divide-gray-100" id="full-notification-list">
                @forelse($notifications as $notification)
                    <div class="relative p-5 hover:bg-gray-50 transition-colors flex items-start gap-4 {{ $notification->is_read ? 'opacity-75' : ($notification->severity === 'danger' ? 'bg-red-50/50' : ($notification->severity === 'warning' ? 'bg-amber-50/50' : 'bg-blue-50/50')) }}" 
                         data-notification-id="{{ $notification->id }}" 
                         data-is-read="{{ $notification->is_read ? 'true' : 'false' }}">
                        
                        <!-- Status Indicator -->
                        <div class="flex-shrink-0 pt-1">
                            @if(!$notification->is_read)
                                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full animate-pulse" data-unread-indicator></div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-gray-900 {{ $notification->is_read ? 'opacity-60' : '' }}">{{ $notification->title }}</p>
                                @if($notification->severity !== 'info')
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded {{ $notification->severity === 'danger' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                                        {{ $notification->severity }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-gray-600 text-sm mt-1 leading-relaxed {{ $notification->is_read ? 'opacity-60' : '' }}">{{ $notification->message }}</p>
                            
                            <div class="flex items-center gap-4 mt-3">
                                <span class="text-gray-400 text-[11px] flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if($notification->type)
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-2 py-0.5 rounded">
                                        {{ str_replace('_', ' ', $notification->type) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 lg:opacity-100 transition-opacity">
                            @if(!$notification->is_read)
                                <button onclick="markAsRead('{{ $notification->id }}', this)" 
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Mark as read"
                                        data-mark-read-btn>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endif
                            <button onclick="deleteNotification('{{ $notification->id }}', this)" 
                                    class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Delete notification">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="text-gray-200 mb-4">
                            <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4m8 4v6" />
                            </svg>
                        </div>
                        <h4 class="text-gray-500 font-bold">No system alerts found</h4>
                        <p class="text-gray-400 text-sm mt-1">You're all caught up!</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

        <!-- Customer Communication Logs -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mt-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    Customer Communications
                </h3>
                <span class="bg-purple-100 text-purple-600 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $logs->total() }} sent</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#5542ff] text-white">
                            <th class="px-6 py-4 text-[11px] font-extrabold uppercase tracking-widest">Customer</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold uppercase tracking-widest">Channel</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold uppercase tracking-widest">Subject</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold uppercase tracking-widest">Sender</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold uppercase tracking-widest">Date</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold uppercase tracking-widest text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $log->customer?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $log->customer?->email ?? $log->customer?->phone }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase {{ $log->channel === 'email' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $log->channel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 font-medium">{{ $log->subject ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600">{{ $log->user?->name ?? 'System' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y h:i A') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button"
                                            class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 transition-all"
                                            onclick="showLogMessage(this.getAttribute('data-subject'), this.getAttribute('data-message'))"
                                            data-subject="{{ $log->subject }}"
                                            data-message="{{ $log->message }}">
                                        View Content
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-gray-200 mb-4">
                                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-gray-500 font-bold">No communication history</h4>
                                    <p class="text-gray-400 text-sm mt-1">Sent messages will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Log Viewer Modal -->
    <div id="log-viewer-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeLogModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="log-modal-subject"></h3>
                            <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-100 min-h-[200px] whitespace-pre-wrap text-sm text-gray-700" id="log-modal-content"></div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" onclick="closeLogModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showLogMessage(subject, message) {
            document.getElementById('log-modal-subject').textContent = subject || 'No Subject';
            document.getElementById('log-modal-content').innerHTML = message;
            document.getElementById('log-viewer-modal').classList.remove('hidden');
        }

        function closeLogModal() {
            document.getElementById('log-viewer-modal').classList.add('hidden');
        }

        // Use global functions from app.blade.php but provide page-specific overrides if needed
        window.markAllNotificationsAsRead = function() {
            fetch('{{ route("notifications.mark-all-as-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); 
                }
            });
        };

        window.deleteAllNotifications = function() {
            if (!confirm('Are you sure you want to delete all notifications?')) return;
            
            fetch('{{ route("notifications.delete-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        };
    </script>
    @endpush
</x-app-layout>