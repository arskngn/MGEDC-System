<section x-data="{ 
    modalOpen: false, 
    selectedImage: '{{ $user->image }}',
    name: '{{ $user->name }}',
    get defaultInitial() {
        return this.name ? this.name.charAt(0).toUpperCase() : '?';
    }
}">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Profile Picture Selection -->
        <div class="flex flex-col items-center space-y-4">
            <div class="relative group">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-100 shadow-sm flex items-center justify-center bg-[#4634ff] text-white text-4xl font-bold uppercase">
                    <template x-if="selectedImage">
                        <img :src="'/' + selectedImage" alt="Profile Picture" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!selectedImage">
                        <span x-text="defaultInitial"></span>
                    </template>
                </div>
                <button type="button" @click="modalOpen = true" class="absolute bottom-0 right-0 p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition-colors border border-gray-200">
                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
            </div>
            <input type="hidden" name="image" :value="selectedImage">
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" x-model="name" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <!-- Profile Picture Selection Modal -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="modalOpen = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-[#0a1233]">Choose Profile Picture</h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-5 gap-4">
                        @foreach(range(1, 10) as $i)
                            @php $filename = "profile-pictures/avatar$i.png"; @endphp
                            <button type="button" @click="selectedImage = '{{ $filename }}'; modalOpen = false" 
                                class="relative group focus:outline-none"
                                :class="selectedImage === '{{ $filename }}' ? 'ring-2 ring-[#4634ff] rounded-full' : ''">
                                <img src="/{{ $filename }}" alt="Avatar {{ $i }}" 
                                    class="w-full h-auto rounded-full hover:opacity-75 transition-opacity">
                            </button>
                        @endforeach
                    </div>
                    <div class="mt-6 flex justify-center">
                        <button type="button" @click="selectedImage = ''; modalOpen = false" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-semibold">
                            Reset to Default
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
