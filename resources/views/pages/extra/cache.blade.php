<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Clear System Cache</h2>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8" x-data="{ clearing: false, message: '' }">
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Cache Clearing Options</h3>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-gray-800 font-medium">Compiled views will be cleared</p>
                            <p class="text-gray-600 text-sm">Removes cached Blade template compilations</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-gray-800 font-medium">Application cache will be cleared</p>
                            <p class="text-gray-600 text-sm">Removes all application-level caches</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-gray-800 font-medium">Route cache will be cleared</p>
                            <p class="text-gray-600 text-sm">Removes cached route definitions</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-gray-800 font-medium">Configuration cache will be cleared</p>
                            <p class="text-gray-600 text-sm">Removes cached configuration values</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-gray-800 font-medium">Compiled services and packages files will be removed</p>
                            <p class="text-gray-600 text-sm">Removes compiled service and package cache files</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-gray-800 font-medium">All caches will be cleared</p>
                            <p class="text-gray-600 text-sm">Comprehensive cache cleanup</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clear Button -->
            <form action="{{ route('extra.cache.clear') }}" method="POST" @submit="clearing = true">
                @csrf
                <button type="submit" class="w-full bg-[#4634ff] hover:bg-[#3828cc] active:bg-[#2f22a8] text-white font-semibold py-3 px-6 rounded-lg transition-all transform hover:translate-y-[-2px] active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="clearing">
                    <span x-show="!clearing">Click to clear</span>
                    <span x-show="clearing" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Clearing...
                    </span>
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
