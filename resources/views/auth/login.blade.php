<x-guest-layout>
    <div class="bg-[#1a2035] shadow-md overflow-hidden sm:rounded-2xl">
        <!-- Header with Custom Shape -->
        <div class="bg-[#4634ff] text-white text-center pt-10 pb-12 px-6 relative flex flex-col items-center" style="clip-path: polygon(0 0, 100% 0, 100% 85%, 50% 100%, 0 85%);">
            <div class="mb-4">
                <a href="/">
                    <x-application-logo type="dark" class="h-20 w-20 rounded-full object-cover border-2 border-white shadow-lg" />
                </a>
            </div>
            <h2 class="text-4xl font-bold mb-1">
                Welcome to {{ $generalSetting->site_title ?? 'MGEDC' }}
            </h2>
        </div>

        <div class="px-8 py-10">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Username" class="text-white mb-2" />
                    <x-text-input id="email"
                        class="block mt-1 w-full bg-[#1a2035] border-gray-600 text-white focus:border-[#4634ff] focus:ring-[#4634ff]"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-6">
                    <div class="flex justify-between items-baseline">
                        <x-input-label for="password" value="Password" class="text-white mb-2" />
                    </div>

                    <div class="relative">
                        <x-text-input id="password"
                            class="block mt-1 w-full bg-[#1a2035] border-gray-600 text-white focus:border-[#4634ff] focus:ring-[#4634ff] pr-10"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

                        <!-- Eye Icon -->
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center z-10 cursor-pointer">
                            <!-- Open Eye Icon (shown when password is hidden) -->
                            <svg id="eyeIcon"
                                class="h-6 w-6 text-gray-400 hover:text-white transition-colors"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Closed Eye Icon (shown when password is visible) -->
                            <svg id="eyeOffIcon"
                                class="h-6 w-6 text-gray-400 hover:text-white transition-colors hidden"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.12 9.12l.841-.84a1 1 0 00-1.414-1.415l-.84.84m7.07-7.07l.841-.84A1 1 0 0012 2.172m0 0a10 10 0 00-7.08 2.92m7.08-2.92l.84-.84a1 1 0 00-1.414-1.414l-.84.84m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </div>
                    </div>

                    <p id="caps-lock-warning"
                        class="text-red-500 text-xs mt-1 hidden">
                        Capslock is on
                    </p>

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Login Button -->
                <div class="mt-8">
                    <button type="submit"
                        class="w-full bg-[#4634ff] hover:bg-[#3828cc] active:bg-[#2f22a8] focus:bg-[#3828cc]
                               text-white py-3 text-lg font-semibold rounded-md
                               shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                        {{ __('Log in') }}
                    </button>
                </div>

                <div class="mt-2 text-center">
                    <a href="{{ url('/') }}" class="text-[10px] uppercase tracking-wider text-gray-500 hover:text-white transition-colors">
                        return home page
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle Password Visibility
        const eyeIcon = document.querySelector('#eyeIcon');
        const eyeOffIcon = document.querySelector('#eyeOffIcon');
        const password = document.querySelector('#password');
        const eyeContainer = document.querySelector('.absolute.inset-y-0');

        eyeContainer.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // toggle icons
            if (type === 'text') {
                // Show eye-off icon when password is visible
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                // Show eye icon when password is hidden
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        });

        // Caps Lock Warning
        const passwordInput = document.querySelector('#password');
        const capsWarning = document.getElementById('caps-lock-warning');

        passwordInput.addEventListener('keyup', function (event) {
            if (event.getModifierState('CapsLock')) {
                capsWarning.classList.remove('hidden');
            } else {
                capsWarning.classList.add('hidden');
            }
        });
    </script>
</x-guest-layout>