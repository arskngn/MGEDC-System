<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-[#0a1233]">Notification to Customer</h2>
            <a href="{{ route('customers.index') }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-semibold inline-flex items-center">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <style>
            [data-notif-channel="sms"] .ck-toolbar { display: none !important; }
            [data-notif-channel="email"] .ck-editor__editable,
            [data-notif-channel="email"] .ck-editor__editable_inline {
                min-height: 320px !important;
            }
        </style>

        <form method="POST" action="{{ route('customers.notifications.all.send') }}" class="bg-gray-50 p-8 rounded-xl border border-gray-200 space-y-8" x-data="{ channel: 'email' }" x-bind:data-notif-channel="channel">
            @csrf
            <input type="hidden" name="channel" :value="channel">

            <div class="flex space-x-4">
                <button
                    type="button"
                    @click="channel = 'email'"
                    :class="channel === 'email' ? 'border-[#4634ff] bg-gray-50' : 'border-gray-200 bg-white'"
                    class="flex-1 flex items-center justify-center space-x-3 py-10 rounded-xl border-2 relative overflow-hidden group transition-all"
                >
                    <div x-show="channel === 'email'"
                         class="absolute top-0 right-0 w-8 h-8 bg-[#4634ff] flex items-center justify-center transform translate-x-4 -translate-y-4 rotate-45">
                        <svg class="w-3 h-3 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <div class="text-center">
                        <svg :class="channel === 'email' ? 'text-[#4634ff]' : 'text-gray-400 group-hover:text-[#4634ff]'"
                             class="w-8 h-8 mx-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M3 8v10a2 2 0 002 2h14a2 2 0 002-2V8" />
                        </svg>
                        <span class="block mt-2 font-bold text-gray-700">Send via Email</span>
                    </div>
                </button>

                <button
                    type="button"
                    @click="channel = 'sms'"
                    :class="channel === 'sms' ? 'border-[#4634ff] bg-gray-50' : 'border-gray-200 bg-white'"
                    class="flex-1 flex items-center justify-center space-x-3 py-10 rounded-xl border-2 relative overflow-hidden group transition-all"
                >
                    <div x-show="channel === 'sms'"
                         class="absolute top-0 right-0 w-8 h-8 bg-[#4634ff] flex items-center justify-center transform translate-x-4 -translate-y-4 rotate-45">
                        <svg class="w-3 h-3 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <div class="text-center">
                        <svg :class="channel === 'sms' ? 'text-[#4634ff]' : 'text-gray-400 group-hover:text-[#4634ff]'"
                             class="w-8 h-8 mx-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="block mt-2 font-bold text-gray-700">Send via SMS</span>
                    </div>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Being Sent To <span class="text-red-500">*</span>
                    </label>
                    <select disabled
                            class="w-full border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-[#4634ff] focus:border-[#4634ff] bg-white">
                        <option>All Customers</option>
                    </select>
                </div>

                <div x-show="channel === 'email'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Subject
                        <span class="text-red-500" x-show="channel === 'email'">*</span>
                    </label>
                    <input type="text"
                           name="subject"
                           placeholder="Subject / Title"
                           class="w-full border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-[#4634ff] focus:border-[#4634ff]"
                           :required="channel === 'email'"
                           :disabled="channel !== 'email'" />
                    @error('subject')
                        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <div x-show="channel === 'email'" x-cloak>
                        <textarea
                            name="email_message"
                            class="ck-editor block w-full border-gray-200 rounded-xl px-4 py-3 text-sm min-h-[280px]"
                            :disabled="channel !== 'email'"
                            :required="channel === 'email'"
                        ></textarea>
                    </div>
                    <div x-show="channel === 'sms'" x-cloak>
                        <textarea
                            name="sms_message"
                            class="block w-full border border-gray-300 bg-gray-50 rounded-xl px-4 py-3 text-sm min-h-[220px]"
                            :disabled="channel !== 'sms'"
                            :required="channel === 'sms'"
                        ></textarea>
                    </div>
                    @error('email_message')
                        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                    @enderror
                    @error('sms_message')
                        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Start Form <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="start_form" min="1" required
                           placeholder="Start form customer id e.g. 1"
                           class="w-full border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-[#4634ff] focus:border-[#4634ff]" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Per Batch <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-stretch gap-2">
                        <input type="number" name="per_batch" min="1" required
                               class="w-full border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-[#4634ff] focus:border-[#4634ff]" />
                        <div class="w-24 border border-gray-200 rounded-xl bg-white text-sm flex items-center justify-center text-gray-700">
                            Customer
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Cooling Period <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-stretch gap-2">
                        <input type="number" name="cooling_period" min="0" required
                               placeholder="Waiting time"
                               class="w-full border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-[#4634ff] focus:border-[#4634ff]" />
                        <div class="w-24 border border-gray-200 rounded-xl bg-white text-sm flex items-center justify-center text-gray-700">
                            Seconds
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all transform hover:translate-y-[-2px] active:translate-y-[0px]">
                    Submit
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.ck-editor').forEach(el => {
                    if (el.dataset.editorInit === '1') return;
                    el.dataset.editorInit = '1';
                    ClassicEditor.create(el).catch(console.error);
                });
            });
        </script>
    @endpush
</x-app-layout>

