<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">System Updates</h2>
        </div>

        <!-- Current Version -->
        <div class="border-2 border-green-500 bg-green-50 rounded-2xl p-8 text-center">
            <div class="text-6xl font-bold text-orange-500 mb-2">1.0</div>
            <p class="text-2xl font-semibold text-gray-800 mb-6">MGEDC V1</p>
            <div class="bg-white rounded-lg p-6">
                <svg class="w-6 h-6 text-blue-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-700 leading-relaxed">
                    <strong>You are currently using the latest version of the system.</strong> We are committed to continuous improvement and are actively developing the next version. Stay tuned for exciting new features and enhancements to be released soon!
                </p>
            </div>
        </div>

        <!-- Update Log -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <button onclick="toggleUpdateLog()" class="w-full flex items-center justify-between p-6 hover:bg-gray-50 transition-colors">
                <h3 class="text-lg font-semibold text-gray-800">Update History</h3>
                <svg id="logToggleIcon" class="w-5 h-5 text-gray-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </button>
            <div id="updateLogContent" class="hidden">
                <div class="border-t border-gray-200 p-6 space-y-6">
                    <!-- Version 1.0 -->
                    <div class="border-l-4 border-[#4634ff] pl-6 py-4">
                        <h4 class="text-lg font-bold text-gray-800 mb-2">Version 1.0 | Uploaded: 2026-03-21</h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li><span class="text-blue-600 font-semibold">[ADD]</span> Automatic System Update</li>
                            <li><span class="text-blue-600 font-semibold">[ADD]</span> Configurable Number of items per Page for Pagination</li>
                            <li><span class="text-blue-600 font-semibold">[ADD]</span> Configurable Currency Display Format</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Admin Dashboard Widget Design</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Notification Sending Process</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> User Experience of the Admin Sidebar</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Improved Menu Searching Functionality on the Admin Panel</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> User Experience of the Select Fields of the Admin Panel</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Centralized Settings System</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Notification Toaster UI</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Notification Template Content Configuration</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Configurable Email From Name and Address for Each Template</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Configurable SMS From for Each Template</li>
                            <li><span class="text-purple-600 font-semibold">[UPDATE]</span> Overall User Interface of the Admin Panel</li>
                            <li><span class="text-red-600 font-semibold">[PATCH]</span> Laravel 11</li>
                            <li><span class="text-red-600 font-semibold">[PATCH]</span> PHP 8.3</li>
                            <li><span class="text-red-600 font-semibold">[PATCH]</span> Latest System Patch</li>
                            <li><span class="text-red-600 font-semibold">[PATCH]</span> Latest Security Patch</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleUpdateLog() {
            const content = document.getElementById('updateLogContent');
            const icon = document.getElementById('logToggleIcon');
            content.classList.toggle('hidden');
            icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    </script>
</x-app-layout>
