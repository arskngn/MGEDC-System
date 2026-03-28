<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">System Configuration</h2>
            <a href="{{ route('settings.index') }}" class="flex items-center text-sm text-gray-500 hover:text-[#4634ff] transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Settings
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('settings.configuration.update') }}" method="POST" class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 space-y-8">
            @csrf

            <!-- Email Notification -->
            <div class="flex items-center justify-between py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Email Notification</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        If you enable this module, the system will send emails to users where needed. Otherwise, no email will be sent. 
                        <span class="text-red-500 font-medium">So be sure before disabling this module that, the system doesn't need to send any emails.</span>
                    </p>
                </div>
                <div x-data="{ enabled: {{ $setting->email_notification ? 'true' : 'false' }} }">
                    <input type="hidden" name="email_notification" :value="enabled ? 1 : 0">
                    <button @click="enabled = !enabled" type="button" :class="{ 'bg-green-500': enabled, 'bg-gray-300': !enabled }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors duration-200 focus:outline-none">
                        <span :class="{ 'translate-x-6': enabled, 'translate-x-1': !enabled }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-200"></span>
                    </button>
                </div>
            </div>

            <!-- SMS Notification -->
            <div class="flex items-center justify-between py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">SMS Notification</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        If you enable this module, the system will send SMS to users where needed. Otherwise, no SMS will be sent. 
                        <span class="text-red-500 font-medium">So be sure before disabling this module that, the system doesn't need to send any SMS.</span>
                    </p>
                </div>
                <div x-data="{ enabled: {{ $setting->sms_notification ? 'true' : 'false' }} }">
                    <input type="hidden" name="sms_notification" :value="enabled ? 1 : 0">
                    <button @click="enabled = !enabled" type="button" :class="{ 'bg-green-500': enabled, 'bg-red-500': !enabled }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors duration-200 focus:outline-none">
                        <span :class="{ 'translate-x-6': enabled, 'translate-x-1': !enabled }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-200"></span>
                    </button>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all transform hover:translate-y-[-2px] active:translate-y-[0px]">
                    Submit
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
