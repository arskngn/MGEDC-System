<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">General Setting</h2>
            <a href="{{ route('settings.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-semibold flex items-center">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Settings
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" 
             x-data="{ 
                selectedCurrency: '{{ old('currency', $setting->currency) }}',
                currencies: @js($currencies),
                updateSymbol() {
                    this.selectedSymbol = this.currencies[this.selectedCurrency] || '';
                },
                selectedSymbol: '{{ old('currency_symbol', $setting->currency_symbol) }}'
             }">
            <form action="{{ route('settings.general.update') }}" method="POST" class="p-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Site Title -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Site Title <span class="text-red-500 ml-1">*</span>
                        </label>
                        <input type="text" name="site_title" value="{{ old('site_title', $setting->site_title) }}" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm {{ !Auth::user()->hasRole('admin') ? 'bg-gray-50 cursor-not-allowed' : '' }}" 
                            {{ !Auth::user()->hasRole('admin') ? 'readonly' : '' }} required>
                        @error('site_title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Full Company Name -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Full Company Name <span class="text-red-500 ml-1">*</span>
                        </label>
                        <input type="text" name="full_company_name" value="{{ old('full_company_name', $setting->full_company_name) }}" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm {{ !Auth::user()->hasRole('admin') ? 'bg-gray-50 cursor-not-allowed' : '' }}" 
                            {{ !Auth::user()->hasRole('admin') ? 'readonly' : '' }} required>
                        @error('full_company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Currency -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Currency <span class="text-red-500 ml-1">*</span>
                        </label>
                        <select name="currency" x-model="selectedCurrency" @change="updateSymbol()"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm appearance-none bg-no-repeat bg-right {{ !Auth::user()->hasRole('admin') ? 'bg-gray-50 cursor-not-allowed pointer-events-none' : '' }}" 
                            style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22m6 8 4 4 4-4%22%2F%3E%3C%2Fsvg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;" 
                            {{ !Auth::user()->hasRole('admin') ? 'disabled' : '' }} required>
                            @foreach($currencies as $code => $symbol)
                                <option value="{{ $code }}">{{ $code }}</option>
                            @endforeach
                        </select>
                        @if(!Auth::user()->hasRole('admin'))
                            <input type="hidden" name="currency" value="{{ $setting->currency }}">
                        @endif
                        @error('currency') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Currency Symbol -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Currency Symbol <span class="text-red-500 ml-1">*</span>
                        </label>
                        <input type="text" name="currency_symbol" x-model="selectedSymbol"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm bg-gray-50 cursor-not-allowed" 
                            readonly required>
                        @error('currency_symbol') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Timezone -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Timezone <span class="text-red-500 ml-1">*</span>
                        </label>
                        <select name="timezone" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm appearance-none bg-no-repeat bg-right" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22m6 8 4 4 4-4%22%2F%3E%3C%2Fsvg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;" required>
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ $setting->timezone == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                        @error('timezone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Records per page -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Record to Display Per page
                        </label>
                        <select name="records_per_page" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm appearance-none bg-no-repeat bg-right" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22m6 8 4 4 4-4%22%2F%3E%3C%2Fsvg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;">
                            <option value="10" {{ $setting->records_per_page == 10 ? 'selected' : '' }}>10 items per page</option>
                            <option value="20" {{ $setting->records_per_page == 20 ? 'selected' : '' }}>20 items per page</option>
                            <option value="50" {{ $setting->records_per_page == 50 ? 'selected' : '' }}>50 items per page</option>
                            <option value="100" {{ $setting->records_per_page == 100 ? 'selected' : '' }}>100 items per page</option>
                        </select>
                        @error('records_per_page') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Currency Format -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Currency Showing Format <span class="text-red-500 ml-1">*</span>
                        </label>
                        <select name="currency_format" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm appearance-none bg-no-repeat bg-right" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22m6 8 4 4 4-4%22%2F%3E%3C%2Fsvg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;" required>
                            <option value="both" {{ $setting->currency_format == 'both' ? 'selected' : '' }}>Show Currency Text and Symbol Both</option>
                            <option value="text" {{ $setting->currency_format == 'text' ? 'selected' : '' }}>Show Currency Text Only</option>
                            <option value="symbol" {{ $setting->currency_format == 'symbol' ? 'selected' : '' }}>Show Currency Symbol Only</option>
                        </select>
                        @error('currency_format') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notification Retention -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            Notification Retention (Days)
                            <svg class="h-4 w-4 ml-1 text-gray-400 cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor" title="Notifications older than this will be automatically deleted to save space.">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </label>
                        <input type="number" name="notification_retention_days" value="{{ old('notification_retention_days', $setting->notification_retention_days) }}" 
                            min="1" max="365"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-0 focus:border-[#4634ff] transition-colors text-sm" 
                            placeholder="Default: 30 days">
                        @error('notification_retention_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-gray-500">Notifications older than this will be auto-deleted.</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-10">
                    <button type="submit" class="w-full bg-[#4634ff] text-white font-bold py-3.5 rounded-lg hover:bg-[#3b2ad6] transition-all shadow-lg shadow-[#4634ff]/20 flex items-center justify-center">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
