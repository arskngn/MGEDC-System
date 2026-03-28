<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Server Information</h2>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div class="space-y-0 divide-y divide-gray-200">
                <!-- PHP Version -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">PHP Version</span>
                    <span class="text-gray-900 font-semibold">{{ $php_version }}</span>
                </div>

                <!-- PHP SAPI -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">PHP SAPI</span>
                    <span class="text-gray-900 font-semibold">{{ ucfirst($php_sapi) }}</span>
                </div>

                <!-- Operating System -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">Operating System</span>
                    <span class="text-gray-900 font-semibold">{{ $operating_system }}</span>
                </div>

                <!-- Server Software -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">Server Software</span>
                    <span class="text-gray-900 font-semibold text-sm">{{ $server_software }}</span>
                </div>

                <!-- Server IP Address -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">Server IP Address</span>
                    <span class="text-gray-900 font-semibold">{{ $server_ip }}</span>
                </div>

                <!-- Server Protocol -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">Server Protocol</span>
                    <span class="text-gray-900 font-semibold">{{ $server_protocol }}</span>
                </div>

                <!-- HTTP Host -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">HTTP Host</span>
                    <span class="text-gray-900 font-semibold">{{ $http_host }}</span>
                </div>

                <!-- Server Port -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">Server Port</span>
                    <span class="text-gray-900 font-semibold">{{ $server_port }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
