<x-app-layout>
    <div class="space-y-6" x-data="{ 
        mainTab: localStorage.getItem('notif_mainTab') || 'global', 
        templateTab: localStorage.getItem('notif_templateTab') || 'email',
        showTestMailModal: false,
        emailMethod: '{{ $setting->email_method ?? 'php' }}',
        smsMethod: '{{ $setting->sms_method ?? 'nexmo' }}',
        activeTemplateId: localStorage.getItem('notif_activeTemplateId') || null,
        emailDropdownOpen: false,
        smsDropdownOpen: false
    }" x-init="
        $watch('mainTab', value => localStorage.setItem('notif_mainTab', value));
        $watch('templateTab', value => localStorage.setItem('notif_templateTab', value));
        $watch('activeTemplateId', value => localStorage.setItem('notif_activeTemplateId', value));
    ">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Notification Settings</h2>
            <a href="{{ route('settings.index') }}" class="flex items-center text-sm text-gray-500 hover:text-[#4634ff] transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Settings
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <!-- Main Tabs -->
            <div class="flex border-b border-gray-200 mb-6">
                <button @click="mainTab = 'global'" :class="mainTab === 'global' ? 'text-[#4634ff] border-b-2 border-[#4634ff]' : 'text-gray-500 hover:text-gray-800'" class="px-6 py-3 text-sm font-semibold transition-all">Global Template</button>
                <button @click="mainTab = 'email_setting'" :class="mainTab === 'email_setting' ? 'text-[#4634ff] border-b-2 border-[#4634ff]' : 'text-gray-500 hover:text-gray-800'" class="px-6 py-3 text-sm font-semibold transition-all">Email Setting</button>
                <button @click="mainTab = 'sms_setting'" :class="mainTab === 'sms_setting' ? 'text-[#4634ff] border-b-2 border-[#4634ff]' : 'text-gray-500 hover:text-gray-800'" class="px-6 py-3 text-sm font-semibold transition-all">SMS Setting</button>
                <button @click="mainTab = 'templates'" :class="mainTab === 'templates' ? 'text-[#4634ff] border-b-2 border-[#4634ff]' : 'text-gray-500 hover:text-gray-800'" class="px-6 py-3 text-sm font-semibold transition-all">Notification Templates</button>
            </div>

            <!-- Global Template Content -->
            <div x-show="mainTab === 'global'" class="space-y-6">
                <div class="flex space-x-4">
                    <button @click="templateTab = 'email'" :class="templateTab === 'email' ? 'border-[#4634ff] bg-gray-50' : 'border-gray-200 bg-white'" class="flex-1 flex items-center justify-center space-x-3 py-10 rounded-xl border-2 relative overflow-hidden group transition-all">
                        <div x-show="templateTab === 'email'" class="absolute top-0 right-0 w-8 h-8 bg-[#4634ff] flex items-center justify-center transform translate-x-4 -translate-y-4 rotate-45">
                            <svg class="w-3 h-3 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="text-center">
                            <svg :class="templateTab === 'email' ? 'text-[#4634ff]' : 'text-gray-400 group-hover:text-[#4634ff]'" class="w-8 h-8 mx-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="block mt-2 font-bold text-gray-700">Email Template</span>
                        </div>
                    </button>
                    <button @click="templateTab = 'sms'" :class="templateTab === 'sms' ? 'border-[#4634ff] bg-gray-50' : 'border-gray-200 bg-white'" class="flex-1 flex items-center justify-center space-x-3 py-10 rounded-xl border-2 relative overflow-hidden group transition-all">
                        <div x-show="templateTab === 'sms'" class="absolute top-0 right-0 w-8 h-8 bg-[#4634ff] flex items-center justify-center transform translate-x-4 -translate-y-4 rotate-45">
                            <svg class="w-3 h-3 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="text-center">
                            <svg :class="templateTab === 'sms' ? 'text-[#4634ff]' : 'text-gray-400 group-hover:text-[#4634ff]'" class="w-8 h-8 mx-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="block mt-2 font-bold text-gray-700">SMS Template</span>
                        </div>
                    </button>
                </div>

                <!-- Shortcodes -->
                <div class="mt-6">
                    <div class="flex justify-between items-center bg-[#4634ff] text-white px-4 py-2 rounded-t-md">
                        <h3 class="text-sm font-bold">Short Code</h3>
                        <h3 class="text-sm font-bold">Description</h3>
                    </div>
                    <div class="border border-gray-200 rounded-b-md">
                        @php
                            $shortcodes = [
                                '{{fullname}}' => 'Full Name of User',
                                '{{username}}' => 'Username of User',
                                '{{message}}' => 'Message',
                                '{{site_name}}' => 'Name of your site',
                                '{{site_currency}}' => 'Currency of your site',
                                '{{currency_symbol}}' => 'Symbol of currency',
                            ];
                        @endphp
                        @foreach($shortcodes as $code => $desc)
                        <div class="flex justify-between items-center px-4 py-3 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                            <p class="text-sm text-gray-800">@{{code}}</p>
                            <div class="flex items-center space-x-4">
                                <p class="text-sm text-gray-500">{{ $desc }}</p>
                                <button type="button" @click="locateShortcode('{{ $code }}')" class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-md hover:bg-gray-300">Locate</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <form action="{{ route('settings.notification.update') }}" method="POST" class="mt-8 space-y-6">
                    @csrf
                    <!-- Email Template Section -->
                    <div x-show="templateTab === 'email'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Sent From - Name <span class="text-red-500">*</span></label>
                                <input type="text" name="email_sent_from_name" value="{{ $setting->email_sent_from_name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Sent From - Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email_sent_from_email" value="{{ $setting->email_sent_from_email }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Body</label>
                                <textarea name="email_body" id="email_body" rows="20" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm font-mono whitespace-pre" oninput="updatePreview()">{{ $setting->email_body }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Preview</label>
                                <div class="mt-1 border border-gray-300 rounded-md overflow-hidden bg-gray-50 h-[420px] lg:h-[460px]">
                                    <iframe id="email_preview" class="w-full h-full border-none"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SMS Template Section -->
                    <div x-show="templateTab === 'sms'" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SMS Sent From <span class="text-red-500">*</span></label>
                            <input type="text" name="sms_sent_from" value="{{ $setting->sms_sent_from }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SMS Body <span class="text-red-500">*</span></label>
                            <textarea name="sms_body" id="sms_body" rows="10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm font-mono">{{ $setting->sms_body }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all transform hover:translate-y-[-2px] active:translate-y-[0px]">
                        Submit
                    </button>
                </form>
            </div>

            <!-- Email Setting Content -->
            <div x-show="mainTab === 'email_setting'" class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Email Notification Settings</h3>
                    <button @click="showTestMailModal = true" class="px-4 py-2 border-2 border-[#4634ff] text-[#4634ff] rounded-lg text-sm font-bold hover:bg-[#4634ff] hover:text-white transition-all flex items-center group/btn">
                        <svg class="w-4 h-4 mr-2 transition-transform group-hover/btn:translate-x-1 group-hover/btn:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        Send Test Mail
                    </button>
                </div>

                <form action="{{ route('settings.notification.update') }}" method="POST" class="bg-gray-50 p-8 rounded-xl border border-gray-200 space-y-8">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Send Method</label>
                            <div class="relative overflow-visible">
                            <input type="hidden" name="email_method" x-model="emailMethod">
                            <button type="button" @click="emailDropdownOpen = !emailDropdownOpen" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-4 text-left flex items-center justify-between focus:ring-2 focus:ring-[#4634ff] focus:border-[#4634ff] transition-all">
                                <span class="font-medium text-gray-700" x-text="emailMethod === 'php' ? 'PHP Mail' : (emailMethod === 'smtp' ? 'SMTP' : (emailMethod === 'sendgrid' ? 'SendGrid API' : 'Mailjet API'))"></span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': emailDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="emailMethod === 'php'" x-transition class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="ml-3 text-xs text-blue-700">
                                        <strong>Note:</strong> PHP Mail on Windows (Laragon) works best if Laragon's mail sender is enabled. If sending fails, it will fall back to logging the email in <code>storage/logs/laravel.log</code>.
                                    </p>
                                </div>
                            </div>
                            
                            <div x-show="emailDropdownOpen" @click.away="emailDropdownOpen = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden top-full left-0">
                                <button type="button" @click="emailMethod = 'php'; emailDropdownOpen = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between border-b border-gray-100">
                                    <span :class="emailMethod === 'php' ? 'text-[#4634ff] font-bold' : 'text-gray-700'">PHP Mail</span>
                                    <svg x-show="emailMethod === 'php'" class="w-5 h-5 text-[#4634ff]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button type="button" @click="emailMethod = 'smtp'; emailDropdownOpen = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between border-b border-gray-100" style="pointer-events: auto;">
                                    <span :class="emailMethod === 'smtp' ? 'text-[#4634ff] font-bold' : 'text-gray-700'">SMTP</span>
                                    <svg x-show="emailMethod === 'smtp'" class="w-5 h-5 text-[#4634ff]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button type="button" @click="emailMethod = 'sendgrid'; emailDropdownOpen = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between border-b border-gray-100">
                                    <span :class="emailMethod === 'sendgrid' ? 'text-[#4634ff] font-bold' : 'text-gray-700'">SendGrid API</span>
                                    <svg x-show="emailMethod === 'sendgrid'" class="w-5 h-5 text-[#4634ff]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button type="button" @click="emailMethod = 'mailjet'; emailDropdownOpen = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between">
                                    <span :class="emailMethod === 'mailjet' ? 'text-[#4634ff] font-bold' : 'text-gray-700'">Mailjet API</span>
                                    <svg x-show="emailMethod === 'mailjet'" class="w-5 h-5 text-[#4634ff]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            </div>
                    </div>

                    <!-- SMTP Config -->
                    <div x-show="emailMethod === 'smtp'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Host</label>
                                <input type="text" name="host" placeholder="e.g. smtp.googlemail.com" value="{{ $smtpConfig['host'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Port</label>
                                <input type="text" name="port" placeholder="e.g. 465" value="{{ $smtpConfig['port'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Encryption</label>
                                <select name="encryption" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                                    <option value="ssl" @selected(($smtpConfig['encryption'] ?? 'tls') === 'ssl')>SSL</option>
                                    <option value="tls" @selected(($smtpConfig['encryption'] ?? 'tls') === 'tls')>TLS</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" placeholder="e.g. your_email@gmail.com" value="{{ $smtpConfig['username'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Password</label>
                                <input type="password" name="password" placeholder="e.g. your_app_password" value="{{ $smtpConfig['password'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                            </div>
                        </div>
                    </div>

                    <!-- SendGrid Config -->
                    <div x-show="emailMethod === 'sendgrid'" x-transition class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">App Key</label>
                        <input type="text" name="app_key" placeholder="SendGrid App Key" value="{{ $setting->email_config['app_key'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff] py-3">
                    </div>

                    <!-- Mailjet Config -->
                    <div x-show="emailMethod === 'mailjet'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Public Key</label>
                            <input type="text" name="public_key" placeholder="Mailjet Public Key" value="{{ $setting->email_config['public_key'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Secret Key</label>
                            <input type="text" name="secret_key" placeholder="Mailjet Secret Key" value="{{ $setting->email_config['secret_key'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all transform hover:translate-y-[-2px] active:translate-y-[0px]">
                        Submit
                    </button>
                </form>
            </div>

            <!-- SMS Setting Content -->
            <div x-show="mainTab === 'sms_setting'" class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">SMS Notification Settings</h3>
                    <div x-data="{ open: false }">
                        <button @click="open = true" class="bg-white border border-[#4634ff] text-[#4634ff] px-6 py-2 rounded-xl font-bold hover:bg-blue-50 transition-all flex items-center shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Send Test SMS
                        </button>

                        <!-- Test SMS Modal -->
                        <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="open = false">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form action="{{ route('settings.notification.test-sms') }}" method="POST">
                                        @csrf
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <h3 class="text-lg font-bold text-gray-900 mb-4">Send Test SMS</h3>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                                                    <input type="text" name="mobile" placeholder="e.g. +1234567890" required class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                                                </div>
                                                <p class="text-xs text-gray-500 italic">Note: Please ensure your SMS provider is correctly configured before sending a test SMS.</p>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-[#4634ff] text-base font-bold text-white hover:bg-[#3828cc] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4634ff] sm:ml-3 sm:w-auto sm:text-sm">
                                                Send Test
                                            </button>
                                            <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4634ff] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('settings.notification.update') }}" method="POST" class="bg-gray-50 p-8 rounded-xl border border-gray-200 space-y-8">
                    @csrf
                    <div x-data="{ open: false }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMS Send Method</label>
                        <div class="relative overflow-visible">
                            <input type="hidden" name="sms_method" x-model="smsMethod">
                            <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-4 text-left flex items-center justify-between focus:ring-2 focus:ring-[#4634ff] focus:border-[#4634ff] transition-all">
                                <span class="font-medium text-gray-700" x-text="smsMethod === 'nexmo' ? 'Nexmo (Vonage)' : (smsMethod === 'twilio' ? 'Twilio' : 'Infobip')"></span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                                <button type="button" @click="smsMethod = 'nexmo'; open = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between border-b border-gray-100">
                                    <span :class="smsMethod === 'nexmo' ? 'text-[#4634ff] font-bold' : 'text-gray-700'">Nexmo (Vonage)</span>
                                    <svg x-show="smsMethod === 'nexmo'" class="w-5 h-5 text-[#4634ff]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button type="button" @click="smsMethod = 'twilio'; open = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between border-b border-gray-100">
                                    <span :class="smsMethod === 'twilio' ? 'text-[#4634ff] font-bold' : 'text-gray-700'">Twilio</span>
                                    <svg x-show="smsMethod === 'twilio'" class="w-5 h-5 text-[#4634ff]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button type="button" @click="smsMethod = 'infobip'; open = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between">
                                    <span :class="smsMethod === 'infobip' ? 'text-[#4634ff] font-bold' : 'text-gray-700'">Infobip</span>
                                    <svg x-show="smsMethod === 'infobip'" class="w-5 h-5 text-[#4634ff]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Nexmo Config -->
                    <div x-show="smsMethod === 'nexmo'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">API Key</label>
                            <input type="text" name="api_key" placeholder="Nexmo API Key" value="{{ $setting->sms_config['api_key'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">API Secret</label>
                            <input type="text" name="api_secret" placeholder="Nexmo API Secret" value="{{ $setting->sms_config['api_secret'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                        </div>
                    </div>

                    <!-- Twilio Config -->
                    <div x-show="smsMethod === 'twilio'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Account SID</label>
                                <input type="text" name="account_sid" placeholder="Twilio Account SID" value="{{ $setting->sms_config['account_sid'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Auth Token</label>
                                <input type="text" name="auth_token" placeholder="Twilio Auth Token" value="{{ $setting->sms_config['auth_token'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">From Number</label>
                            <input type="text" name="from_number" placeholder="Twilio From Number (e.g. +1234567890)" value="{{ $setting->sms_config['from_number'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                        </div>
                    </div>

                    <!-- Infobip Config -->
                    <div x-show="smsMethod === 'infobip'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">API Key</label>
                            <input type="text" name="api_key" placeholder="Infobip API Key" value="{{ $setting->sms_config['api_key'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Base URL</label>
                            <input type="text" name="base_url" placeholder="e.g. https://xyz.api.infobip.com" value="{{ $setting->sms_config['base_url'] ?? '' }}" class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff]">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all transform hover:translate-y-[-2px] active:translate-y-[0px]">
                        Submit
                    </button>
                </form>
            </div>

            <!-- Templates Content -->
            <div x-show="mainTab === 'templates'" class="space-y-6">
                <div x-show="!activeTemplateId || activeTemplateId == 'null'">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Notification Templates</h3>
                    </div>
                    <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[#4634ff]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Edit Template</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($templates as $template)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $template->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->subject }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <div class="inline-flex rounded-md shadow-sm" role="group">
                                            <button @click="activeTemplateId = {{ $template->id }}; templateTab = 'email'" type="button" class="px-4 py-2 text-sm font-medium text-[#4634ff] bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-[#3828cc] focus:z-10 focus:ring-2 focus:ring-[#4634ff] focus:text-[#3828cc]">
                                                Email
                                            </button>
                                            <div @click="activeTemplateId = {{ $template->id }}; templateTab = 'email'" class="cursor-pointer px-4 py-2 text-sm font-medium border-t border-b border-r border-gray-200 rounded-r-lg {{ $template->is_email_enabled ? 'bg-[#4634ff] text-white' : 'bg-red-500 text-white' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($template->is_email_enabled)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    @endif
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="inline-flex rounded-md shadow-sm" role="group">
                                            <button @click="activeTemplateId = {{ $template->id }}; templateTab = 'sms'" type="button" class="px-4 py-2 text-sm font-medium text-[#4634ff] bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-[#3828cc] focus:z-10 focus:ring-2 focus:ring-[#4634ff] focus:text-[#3828cc]">
                                                SMS
                                            </button>
                                            <div @click="activeTemplateId = {{ $template->id }}; templateTab = 'sms'" class="cursor-pointer px-4 py-2 text-sm font-medium border-t border-b border-r border-gray-200 rounded-r-lg {{ $template->is_sms_enabled ? 'bg-[#4634ff] text-white' : 'bg-red-500 text-white' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($template->is_sms_enabled)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    @endif
                                                </svg>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @foreach($templates as $template)
                <div x-show="activeTemplateId == {{ $template->id }}" 
                     x-data="{ 
                        emailEnabled: {{ $template->is_email_enabled ? 'true' : 'false' }},
                        smsEnabled: {{ $template->is_sms_enabled ? 'true' : 'false' }},
                        pushEnabled: {{ $template->is_push_enabled ? 'true' : 'false' }}
                     }"
                     class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800">{{ $template->name }}</h3>
                        <button @click="activeTemplateId = null" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50 flex items-center shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </button>
                    </div>

                    <!-- Template Tabs -->
                    <div class="flex space-x-4 mb-6">
                        <button @click="templateTab = 'email'" :class="templateTab === 'email' ? 'border-[#4634ff] bg-gray-50' : 'border-gray-200 bg-white'" class="flex-1 flex flex-col items-center justify-center py-6 rounded-xl border-2 relative overflow-hidden group transition-all">
                            <div x-show="templateTab === 'email'" class="absolute top-0 right-0 w-8 h-8 bg-[#4634ff] flex items-center justify-center transform translate-x-4 -translate-y-4 rotate-45">
                                <svg class="w-3 h-3 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <svg :class="templateTab === 'email' ? 'text-[#4634ff]' : 'text-gray-400 group-hover:text-[#4634ff]'" class="w-6 h-6 mx-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="mt-2 font-bold text-gray-700">Email Template</span>
                        </button>
                        <button @click="templateTab = 'sms'" :class="templateTab === 'sms' ? 'border-[#4634ff] bg-gray-50' : 'border-gray-200 bg-white'" class="flex-1 flex flex-col items-center justify-center py-6 rounded-xl border-2 relative overflow-hidden group transition-all">
                            <div x-show="templateTab === 'sms'" class="absolute top-0 right-0 w-8 h-8 bg-[#4634ff] flex items-center justify-center transform translate-x-4 -translate-y-4 rotate-45">
                                <svg class="w-3 h-3 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <svg :class="templateTab === 'sms' ? 'text-[#4634ff]' : 'text-gray-400 group-hover:text-[#4634ff]'" class="w-6 h-6 mx-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="mt-2 font-bold text-gray-700">SMS Template</span>
                        </button>
                        <button @click="templateTab = 'push'" :class="templateTab === 'push' ? 'border-[#4634ff] bg-gray-50' : 'border-gray-200 bg-white'" class="flex-1 flex flex-col items-center justify-center py-6 rounded-xl border-2 relative overflow-hidden group transition-all">
                            <div x-show="templateTab === 'push'" class="absolute top-0 right-0 w-8 h-8 bg-[#4634ff] flex items-center justify-center transform translate-x-4 -translate-y-4 rotate-45">
                                <svg class="w-3 h-3 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <svg :class="templateTab === 'push' ? 'text-[#4634ff]' : 'text-gray-400 group-hover:text-[#4634ff]'" class="w-6 h-6 mx-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="mt-2 font-bold text-gray-700">Push Notification Template</span>
                        </button>
                    </div>

                    <!-- Template Shortcodes -->
                    <div class="mt-6 mb-6">
                        <div class="flex justify-between items-center bg-[#4634ff] text-white px-4 py-2 rounded-t-md">
                            <h3 class="text-sm font-bold">Short Code</h3>
                            <h3 class="text-sm font-bold text-right">Description</h3>
                        </div>
                        <div class="border border-gray-200 rounded-b-md">
                            @php
                                $shortcodesData = is_string($template->shortcodes) ? json_decode($template->shortcodes, true) : $template->shortcodes;
                            @endphp
                            @if(!empty($shortcodesData) && is_array($shortcodesData))
                                @foreach($shortcodesData as $code => $desc)
                                <div class="flex justify-between items-center px-4 py-3 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                                    <p class="text-sm text-gray-800">@{{code}}</p>
                                    <p class="text-sm text-gray-500">{{ $desc }}</p>
                                </div>
                                @endforeach
                            @else
                                <div class="px-4 py-3 text-sm text-gray-500 italic">No specific shortcodes available for this template.</div>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('settings.notification.template.update', $template->id) }}" method="POST">
                        @csrf
                        <!-- Email Template Section -->
                        <div x-show="templateTab === 'email'" class="space-y-6">
                            <div class="bg-[#4634ff] text-white px-6 py-3 rounded-t-xl font-bold">
                                Email Template
                            </div>
                            <div class="bg-white border border-gray-200 rounded-b-xl p-6 shadow-sm space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
                                        <input type="text" name="subject" value="{{ $template->subject }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-6 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                Status
                                            </span>
                                            <div class="flex-1 relative">
                                                <input type="hidden" name="is_email_enabled" :value="emailEnabled ? '1' : '0'">
                                                <button type="button" 
                                                    @click="emailEnabled = !emailEnabled"
                                                    :class="emailEnabled ? 'bg-[#28c76f]' : 'bg-[#ea4335]'"
                                                    class="w-full text-white font-bold py-2 rounded-r-md transition-all duration-300 flex items-center justify-center relative overflow-hidden group">
                                                    
                                                    <!-- Dark Bar Indicator -->
                                                    <div :class="emailEnabled ? 'right-0' : 'left-0'" 
                                                         class="absolute top-0 bottom-0 w-3 bg-[#1d1d1d] transition-all duration-300"></div>
                                                    
                                                    <span x-text="emailEnabled ? 'Send Email' : 'Don\'t Send'" class="relative z-10"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email Sent From - Name</label>
                                        <input type="text" name="email_sent_from_name" value="{{ $template->email_sent_from_name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                                        <p class="mt-1 text-xs text-blue-500 italic">ⓘ Make the field empty if you want to use global template's name as email sent from name.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email Sent From - Email</label>
                                        <input type="email" name="email_sent_from_email" value="{{ $template->email_sent_from_email }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                                        <p class="mt-1 text-xs text-blue-500 italic">ⓘ Make the field empty if you want to use global template's email as email sent from email.</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Message <span class="text-red-500">*</span></label>
                                    <div class="mt-1">
                                        <textarea name="email_body" id="email_editor_{{ $template->id }}" class="ck-editor">{{ $template->email_body }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SMS Template Section -->
                        <div x-show="templateTab === 'sms'" class="space-y-6">
                            <div class="bg-[#4634ff] text-white px-6 py-3 rounded-t-xl font-bold">
                                SMS Template
                            </div>
                            <div class="bg-white border border-gray-200 rounded-b-xl p-6 shadow-sm space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMS Sent From</label>
                                        <input type="text" name="sms_sent_from" value="{{ $template->sms_sent_from }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                                        <p class="mt-1 text-xs text-blue-500 italic">ⓘ Make the field empty if you want to use global template's name as sms sent from name.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-6 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                Status
                                            </span>
                                            <div class="flex-1 relative">
                                                <input type="hidden" name="is_sms_enabled" :value="smsEnabled ? '1' : '0'">
                                                <button type="button" 
                                                    @click="smsEnabled = !smsEnabled"
                                                    :class="smsEnabled ? 'bg-[#28c76f]' : 'bg-[#ea4335]'"
                                                    class="w-full text-white font-bold py-2 rounded-r-md transition-all duration-300 flex items-center justify-center relative overflow-hidden group">
                                                    
                                                    <!-- Dark Bar Indicator -->
                                                    <div :class="smsEnabled ? 'right-0' : 'left-0'" 
                                                         class="absolute top-0 bottom-0 w-3 bg-[#1d1d1d] transition-all duration-300"></div>
                                                    
                                                    <span x-text="smsEnabled ? 'Send SMS' : 'Don\'t Send'" class="relative z-10"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Message <span class="text-red-500">*</span></label>
                                    <textarea name="sms_body" rows="10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm font-mono">{{ $template->sms_body }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Push Notification Section -->
                        <div x-show="templateTab === 'push'" class="space-y-6">
                            <div class="bg-[#4634ff] text-white px-6 py-3 rounded-t-xl font-bold">
                                Push Notification Template
                            </div>
                            <div class="bg-white border border-gray-200 rounded-b-xl p-6 shadow-sm space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Notification Title</label>
                                        <input type="text" name="push_title" value="{{ $template->push_title }}" placeholder="Notification Title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                                        <p class="mt-1 text-xs text-blue-500 italic">ⓘ Make the field empty if you want to use global template's title as notification title.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-6 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                Status
                                            </span>
                                            <div class="flex-1 relative">
                                                <input type="hidden" name="is_push_enabled" :value="pushEnabled ? '1' : '0'">
                                                <button type="button" 
                                                    @click="pushEnabled = !pushEnabled"
                                                    :class="pushEnabled ? 'bg-[#28c76f]' : 'bg-[#ea4335]'"
                                                    class="w-full text-white font-bold py-2 rounded-r-md transition-all duration-300 flex items-center justify-center relative overflow-hidden group">
                                                    
                                                    <!-- Dark Bar Indicator -->
                                                    <div :class="pushEnabled ? 'right-0' : 'left-0'" 
                                                         class="absolute top-0 bottom-0 w-3 bg-[#1d1d1d] transition-all duration-300"></div>
                                                    
                                                    <span x-text="pushEnabled ? 'Send Push Notify' : 'Don\'t Send'" class="relative z-10"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Message <span class="text-red-500">*</span></label>
                                    <textarea name="push_body" rows="10" placeholder="Your message using short-codes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm font-mono">{{ $template->push_body }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all transform hover:translate-y-[-2px] active:translate-y-[0px]">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Centralized Test Mail Modal -->
        <div x-show="showTestMailModal" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background Overlay -->
                <div @click="showTestMailModal = false" class="fixed inset-0 transition-opacity bg-gray-900/75" aria-hidden="true"></div>

                <!-- Modal Positioning Hack -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="showTestMailModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="inline-block w-full max-w-lg p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8">
                    
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Test Mail Setup</h3>
                        <button @click="showTestMailModal = false" class="text-gray-400 hover:text-red-500 transition-colors p-2 hover:bg-red-50 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('settings.notification.test-mail') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sent to</label>
                            <input type="email" name="email" placeholder="Email Address" required class="w-full border-gray-300 rounded-xl focus:ring-[#4634ff] focus:border-[#4634ff] py-3">
                        </div>
                        <button type="submit" class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all">
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script>
        function updatePreview() {
            const body = document.getElementById('email_body').value;
            const preview = document.getElementById('email_preview');
            if (preview) {
                const doc = preview.contentDocument || preview.contentWindow.document;
                doc.open();
                doc.write(body);
                doc.close();
            }
        }

        function locateShortcode(shortcode) {
            const activeTemplate = document.querySelector('[x-data]').__x.$data.activeTemplate;
            const textareaId = activeTemplate === 'email' ? 'email_body' : 'sms_body';
            const textarea = document.getElementById(textareaId);
            if (!textarea) return;

            const value = textarea.value;
            const regex = new RegExp(shortcode.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
            let match;
            let lastMatch = null;

            while ((match = regex.exec(value)) !== null) {
                lastMatch = match;
            }

            if (lastMatch) {
                textarea.focus();
                textarea.setSelectionRange(lastMatch.index, lastMatch.index + lastMatch[0].length);
                const lineHeight = 20;
                const lines = value.substring(0, lastMatch.index).split('\n').length;
                textarea.scrollTop = (lines - 5) * lineHeight;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updatePreview, 100);

            // Initialize CKEditor for all templates
            document.querySelectorAll('.ck-editor').forEach(el => {
                ClassicEditor
                    .create(el)
                    .catch(error => {
                        console.error(error);
                    });
            });
        });
    </script>
    @endpush
</x-app-layout>