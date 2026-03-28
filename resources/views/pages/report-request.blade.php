<x-app-layout>
    <div id="report-request-page" x-data="{ 
        modalOpen: false, 
        deleteModalOpen: false,
        modalType: 'Report Bug', 
        modalMessage: '', 
        modalStatus: 'Submitted',
        modalTitle: 'Submit Report',
        modalAction: '{{ route('report-request.store') }}',
        modalBtnText: 'Submit',
        selectedReport: null,
        isAdmin: {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }},
        openReportModal(type) {
            this.modalOpen = true;
            this.modalType = type;
            this.modalMessage = '';
            this.modalStatus = 'Submitted';
            this.modalTitle = 'Submit ' + type;
            this.modalAction = '{{ route('report-request.store') }}';
            this.modalBtnText = 'Submit';
        },
        openEditModal(report) {
            this.modalOpen = true;
            this.modalType = report.type;
            this.modalMessage = report.message;
            this.modalStatus = report.status;
            this.modalTitle = 'Edit Report/Request';
            this.modalAction = '/report-request/update/' + report.id;
            this.modalBtnText = 'Update';
        },
        openDeleteModal(report) {
            this.selectedReport = report;
            this.deleteModalOpen = true;
        }
    }" class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Your Listed Report & Request</h2>
            <div class="flex gap-3">
                <button type="button" @click="openReportModal('Report Bug')" class="flex items-center px-4 py-2 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Report a Bug
                </button>
                <button type="button" @click="openReportModal('Feature Request')" class="flex items-center px-4 py-2 text-sm font-semibold text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Request for Support
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Reports Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#4634ff] to-[#5847ff] text-white">
                            @if(Auth::user()->hasRole('admin'))
                                <th class="px-6 py-4 text-left text-sm font-semibold">User</th>
                            @endif
                            <th class="px-6 py-4 text-left text-sm font-semibold">Type</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Message</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold">Status</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @if($reports->count() > 0)
                            @foreach($reports as $report)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    @if(Auth::user()->hasRole('admin'))
                                        <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                                            {{ $report->user->name ?? 'N/A' }}
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                                        {{ $report->type == 'Report Bug' ? 'bug' : 'request' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <span class="line-clamp-2">{{ $report->message }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $statusStyles = [
                                                'Submitted' => 'border-blue-400 text-blue-600',
                                                'In Progress' => 'border-yellow-400 text-yellow-600',
                                                'Resolved' => 'border-green-400 text-green-600',
                                                'Closed' => 'border-gray-400 text-gray-600',
                                            ];
                                            $currentStyle = $statusStyles[$report->status] ?? $statusStyles['Closed'];
                                        @endphp
                                        <span class="inline-block px-4 py-1 text-xs font-semibold rounded-full border {{ $currentStyle }}">
                                            {{ $report->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        @if(Auth::id() == $report->user_id || Auth::user()->hasRole('admin'))
                                            <button @click="openEditModal({!! htmlspecialchars(json_encode($report), ENT_QUOTES, 'UTF-8') !!})" 
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="openDeleteModal({{ $report->toJson() }})" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No actions</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-gray-500 text-lg">No reports or requests yet</p>
                                    <p class="text-gray-400 text-sm mt-2">Submit a bug report or feature request to get started</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $reports->links() }}
            </div>
        </div>

        <!-- Report & Request Modal -->
        <div id="reportModal" 
            x-show="modalOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50" 
            x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all" 
                x-show="modalOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.away="modalOpen = false">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 x-text="modalTitle" class="text-lg font-bold text-gray-800"></h3>
                    <button @click="modalOpen = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="modalAction" method="POST" class="p-6 space-y-4">
                    @csrf
                    
                    <!-- Type Dropdown -->
                    <div x-data="{ open: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Type <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full px-4 py-2 text-left bg-white border-2 border-gray-300 rounded-lg focus:border-[#4634ff] focus:outline-none transition-colors flex items-center justify-between">
                                <span x-text="modalType"></span>
                                <svg class="w-5 h-5 text-gray-600 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 overflow-hidden">
                                <button type="button" @click="modalType = 'Report Bug'; open = false" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors">
                                    Report Bug
                                </button>
                                <button type="button" @click="modalType = 'Feature Request'; open = false" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors border-t border-gray-200">
                                    Feature Request
                                </button>
                            </div>

                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="type" :value="modalType" />
                        </div>
                        @error('type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Message Textarea -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" x-model="modalMessage" rows="5" placeholder="Describe your bug or feature request..." 
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#4634ff] focus:outline-none resize-none transition-colors"
                            required></textarea>
                        @error('message')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status (Admins Only) -->
                    <div x-show="isAdmin && modalBtnText === 'Update'" x-data="{ open: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full px-4 py-2 text-left bg-white border-2 border-gray-300 rounded-lg focus:border-[#4634ff] focus:outline-none transition-colors flex items-center justify-between">
                                <span x-text="modalStatus"></span>
                                <svg class="w-5 h-5 text-gray-600 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 overflow-hidden">
                                @foreach(['Submitted', 'In Progress', 'Resolved', 'Closed'] as $status)
                                    <button type="button" @click="modalStatus = '{{ $status }}'; open = false" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors border-t border-gray-200">
                                        {{ $status }}
                                    </button>
                                @endforeach
                            </div>

                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="status" :value="modalStatus" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" x-text="modalBtnText" class="w-full bg-[#4634ff] hover:bg-[#3828cc] text-white font-semibold py-3 px-6 rounded-lg transition-all transform hover:translate-y-[-2px] active:translate-y-0">
                    </button>
                </form>
            </div>
        </div>
        <!-- Delete Confirmation Modal -->
        <div x-show="deleteModalOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50" 
            x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all" 
                @click.away="deleteModalOpen = false">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-[#0a1233]">Confirmation Alert!</h3>
                    <button @click="deleteModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600">Are you sure you want to delete this report/request?</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-2xl">
                    <button @click="deleteModalOpen = false" class="px-6 py-2 bg-[#0a1233] text-white rounded-md hover:bg-opacity-90 transition-all text-sm font-bold">No</button>
                    <form :action="'{{ route('report-request.destroy', ['reportRequest' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', selectedReport ? selectedReport.id : '')" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-[#4634ff] text-white rounded-md hover:bg-blue-700 transition-all text-sm font-bold">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden element for old values -->
    <div id="old-values" 
         data-has-errors="{{ $errors->any() ? 'true' : 'false' }}" 
         data-type="{{ old('type', 'Report Bug') }}" 
         data-message="{{ old('message', '') }}" 
         style="display: none;">
    </div>

    <script>
        // Auto-open modal if there are errors
        document.addEventListener('alpine:init', () => {
            const oldValues = document.getElementById('old-values');
            if (oldValues && oldValues.dataset.hasErrors === 'true') {
                const oldType = oldValues.dataset.type;
                const oldMessage = oldValues.dataset.message;
                
                // Delay slightly to ensure Alpine has initialized the component
                setTimeout(() => {
                    const el = document.getElementById('report-request-page');
                    if (el && el.__x && el.__x.$data) {
                        el.__x.$data.openReportModal(oldType);
                        el.__x.$data.modalMessage = oldMessage;
                    } else if (window.Alpine) {
                        // Alpine v3 way
                        const data = Alpine.$data(el);
                        if (data) {
                            data.openReportModal(oldType);
                            data.modalMessage = oldMessage;
                        }
                    }
                }, 100);
            }
        });
    </script>
</x-app-layout>
