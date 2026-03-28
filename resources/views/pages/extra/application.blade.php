<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Application Information</h2>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div class="space-y-0 divide-y divide-gray-200">
                <!-- Application Version -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">{{ $app_name }} Version</span>
                    <span class="text-gray-900 font-semibold">V1.0</span>
                </div>

                <!-- Laravel Version -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">Laravel Version</span>
                    <span class="text-gray-900 font-semibold">{{ $laravel_version }}</span>
                </div>

                <!-- Timezone -->
                <div class="flex items-center justify-between py-4">
                    <span class="text-gray-700 font-medium">Timezone</span>
                    <span class="text-gray-900 font-semibold">{{ $timezone }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
