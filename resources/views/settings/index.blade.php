<x-app-layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">System Settings</h2>
        </div>

        <!-- Search Bar -->
        <div class="max-w-full">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#4634ff] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="settingsSearch" placeholder="Search..." 
                    class="block w-full pl-11 pr-10 py-3 border border-gray-300 rounded-xl leading-5 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-[#4634ff] transition-all duration-200 shadow-sm text-sm">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <button id="clearSearch" class="hidden text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="settingsGrid">
            <!-- General Setting -->
            <a href="{{ route('settings.general') }}" 
               data-keywords="site title, currency, currency symbol, timezone, record per page, currency format, fundamental information"
               class="setting-card group bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#4634ff]/30 transition-all duration-300 relative overflow-hidden">
                <div class="flex items-start space-x-5 relative z-10">
                    <div class="flex-none p-4 bg-[#4634ff] rounded-xl text-white shadow-lg shadow-[#4634ff]/20">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-[#4634ff] transition-colors">General Setting</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Configure the fundamental information of the site.</p>
                        <div class="mt-2 hidden search-hints">
                            <p class="text-xs text-blue-500 italic"></p>
                        </div>
                    </div>
                </div>
                <!-- Background Decoration -->
                <div class="absolute -bottom-6 -right-6 text-[#4634ff]/5 transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                    <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                </div>
            </a>

            <!-- Logo and Favicon -->
            @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('settings.logo-favicon') }}" 
               data-keywords="logo light, logo dark, favicon, login background, site identity, images"
               class="setting-card group bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#4634ff]/30 transition-all duration-300 relative overflow-hidden">
                <div class="flex items-start space-x-5 relative z-10">
                    <div class="flex-none p-4 bg-[#4634ff] rounded-xl text-white shadow-lg shadow-[#4634ff]/20">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-[#4634ff] transition-colors">Logo and Favicon</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Upload your logo and favicon here.</p>
                        <div class="mt-2 hidden search-hints">
                            <p class="text-xs text-blue-500 italic"></p>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 text-[#4634ff]/5 transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                    <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </a>
            @endif

            <!-- System Configuration -->
            <a href="{{ route('settings.configuration') }}" 
               data-keywords="email notification, sms notification, module control, basic modules"
               class="setting-card group bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#4634ff]/30 transition-all duration-300 relative overflow-hidden">
                <div class="flex items-start space-x-5 relative z-10">
                    <div class="flex-none p-4 bg-[#4634ff] rounded-xl text-white shadow-lg shadow-[#4634ff]/20">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-[#4634ff] transition-colors">System Configuration</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Control all of the basic modules of the system.</p>
                        <div class="mt-2 hidden search-hints">
                            <p class="text-xs text-blue-500 italic"></p>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 text-[#4634ff]/5 transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                    <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
            </a>

            <!-- Notification Setting -->
            <a href="{{ route('settings.notification') }}" 
               data-keywords="global template, email setting, sms setting, notification templates, short code, fullname, username, message, site name, site currency, currency symbol"
               class="setting-card group bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#4634ff]/30 transition-all duration-300 relative overflow-hidden">
                <div class="flex items-start space-x-5 relative z-10">
                    <div class="flex-none p-4 bg-[#4634ff] rounded-xl text-white shadow-lg shadow-[#4634ff]/20">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-[#4634ff] transition-colors">Notification Setting</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Control and configure overall notification elements of the system.</p>
                        <div class="mt-2 hidden search-hints">
                            <p class="text-xs text-blue-500 italic"></p>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 text-[#4634ff]/5 transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                    <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
            </a>

            <!-- Extensions -->
            @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('settings.extensions') }}" 
               data-keywords="extensions, extra features, plugins, addons"
               class="setting-card group bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#4634ff]/30 transition-all duration-300 relative overflow-hidden">
                <div class="flex items-start space-x-5 relative z-10">
                    <div class="flex-none p-4 bg-[#4634ff] rounded-xl text-white shadow-lg shadow-[#4634ff]/20">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-[#4634ff] transition-colors">Extensions</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Manage extensions of the system here to extend some extra features.</p>
                        <div class="mt-2 hidden search-hints">
                            <p class="text-xs text-blue-500 italic"></p>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 text-[#4634ff]/5 transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                    <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                    </svg>
                </div>
            </a>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('settingsSearch');
            const clearBtn = document.getElementById('clearSearch');
            const cards = document.querySelectorAll('.setting-card');
            const grid = document.getElementById('settingsGrid');

            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                
                // Toggle clear button
                if (term.length > 0) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }

                cards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const desc = card.querySelector('p').textContent.toLowerCase();
                    const keywords = card.getAttribute('data-keywords').toLowerCase();
                    const hintsContainer = card.querySelector('.search-hints');
                    const hintsText = hintsContainer.querySelector('p');
                    
                    const titleMatch = title.includes(term);
                    const descMatch = desc.includes(term);
                    const keywordMatch = keywords.includes(term);

                    if (titleMatch || descMatch || keywordMatch) {
                        card.classList.remove('hidden');
                        
                        // Handle hints for keyword matches
                        if (keywordMatch && !titleMatch && !descMatch && term.length > 0) {
                            const matchingKeywords = keywords.split(', ')
                                .filter(k => k.includes(term))
                                .join(', ');
                            hintsText.textContent = `Matches: ${matchingKeywords}`;
                            hintsContainer.classList.remove('hidden');
                        } else {
                            hintsContainer.classList.add('hidden');
                        }

                        // Highlight matches
                        if (term.length > 0) {
                            highlightText(card.querySelector('h3'), term);
                            highlightText(card.querySelector('p'), term);
                        } else {
                            resetHighlight(card.querySelector('h3'));
                            resetHighlight(card.querySelector('p'));
                            hintsContainer.classList.add('hidden');
                        }
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            });

            function highlightText(element, term) {
                const text = element.textContent;
                const regex = new RegExp(`(${term})`, 'gi');
                element.innerHTML = text.replace(regex, '<mark class="bg-yellow-200 rounded px-0.5">$1</mark>');
            }

            function resetHighlight(element) {
                element.innerHTML = element.textContent;
            }
        });
    </script>
    @endpush
</x-app-layout>