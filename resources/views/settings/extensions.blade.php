<x-app-layout>
    <div x-data="{ 
        editModalOpen: false, 
        helpModalOpen: false, 
        selectedExtension: null,
        
        openEditModal(extension) {
            this.selectedExtension = extension;
            this.editModalOpen = true;
        },
        openHelpModal(extension) {
            this.selectedExtension = extension;
            this.helpModalOpen = true;
        }
    }">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-[#0a1233]">Extensions</h2>
            <div class="relative flex">
                <input type="text" id="extensionSearch" placeholder="Search..." class="pl-4 pr-10 py-2 border border-gray-200 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm w-64">
                <button class="px-3 bg-[#4634ff] text-white rounded-r-lg hover:bg-blue-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#4634ff] text-white">
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">Extension</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($extensions as $extension)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm flex-none p-1">
                                        @if($extension->image && file_exists(public_path($extension->image)))
                                            <img src="{{ asset($extension->image) }}" class="h-full w-full object-contain">
                                        @else
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $extension->name }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $extension->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($extension->status)
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-green-500 text-green-500 bg-green-50">
                                        Enabled
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-orange-500 text-orange-500 bg-orange-50">
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="openEditModal({{ $extension->toJson() }})" class="inline-flex items-center px-4 py-1.5 bg-white border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Configure
                                    </button>

                                    <button @click="openHelpModal({{ $extension->toJson() }})" class="inline-flex items-center px-4 py-1.5 bg-white border border-gray-800 text-gray-800 rounded-md hover:bg-gray-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Help
                                    </button>

                                    <form action="{{ route('settings.extensions.toggle', $extension) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @if($extension->status)
                                            <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-white border border-red-500 text-red-500 rounded-md hover:bg-red-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                                </svg>
                                                Disable
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-white border border-green-500 text-green-500 rounded-md hover:bg-green-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Enable
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">No extensions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Centralized Update Extension Modal -->
        <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="editModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Update Extension: <span x-text="selectedExtension ? selectedExtension.name : ''"></span></h3>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form :action="'{{ route('settings.extensions.update', ['extension' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', selectedExtension ? selectedExtension.id : '')" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <template x-if="selectedExtension">
                                <div>
                                    <template x-for="(value, key) in selectedExtension.shortcode" :key="key">
                                        <div class="mb-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-1" x-text="key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"></label>
                                            <input type="text" :name="key" :value="value" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="w-full py-2.5 bg-[#4634ff] text-white rounded-lg hover:bg-blue-700 transition-colors font-bold">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Centralized Help Modal -->
        <div x-show="helpModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="helpModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Need Help?</h3>
                        <button @click="helpModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">Key location is shown below</p>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                            <p class="text-sm text-blue-700" x-text="selectedExtension ? selectedExtension.help_text : ''"></p>
                        </div>
                        <template x-if="selectedExtension && selectedExtension.slug === 'google-recaptcha-2'">
                            <div class="mt-4">
                                <img src="{{ asset('system-images/recaptcha-help.png') }}" alt="Help" class="w-full rounded-lg border border-gray-200">
                            </div>
                        </template>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button @click="helpModalOpen = false" class="px-6 py-2 bg-[#0a1233] text-white rounded-md hover:bg-opacity-90 transition-all text-sm font-bold">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('extensionSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    </script>
    @endpush
</x-app-layout>