@php
    $bgImage = null;
    if (isset($generalSetting) && $generalSetting->login_background) {
        $bgImage = asset($generalSetting->login_background);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $generalSetting->site_title ?? config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        @if(isset($generalSetting) && $generalSetting->favicon)
            <link rel="icon" type="image/x-icon" href="{{ asset($generalSetting->favicon) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background-color: #0d2a5c;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
            }
        </style>
        @if($bgImage)
            <style>
                body { background-image: url("{{ $bgImage }}"); }
            </style>
        @else
            <style>
                body { background-image: url("data:image/svg+xml;base64,{{ base64_encode('<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\' viewBox=\'0 0 100 100\'><g fill-rule=\'evenodd\'><g fill=\'#1a3973\' fill-opacity=\'0.4\'><path opacity=\'.5\' d=\'M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10-10v-9h-9v9h9zm0-10v-9h-9v9h9zm0-10v-9h-9v9h9zm0-10v-9h-9v9h9zm0-10v-9h-9v9h9zm0-10v-9h-9v9h9zm0-10v-9h-9v9h9zm0-10v-9h-9v9h9zm0-10v-9h-9v9h9zm10-10v-9h-9v9h9zm10 0v-9h-9v9h9zm10 0v-9h-9v9h9zm10 0v-9h-9v9h9zm10 0v-9h-9v9h9zm10 0v-9h-9v9h9zm10 0v-9h-9v9h9zm10 0v-9h-9v9h9zm10 0v-9h-9v9h9zm-10 10v-9h-9v9h9zm0 10v-9h-9v9h9zm0 10v-9h-9v9h9zm0 10v-9h-9v9h9zm0 10v-9h-9v9h9zm0 10v-9h-9v9h9zm0 10v-9h-9v9h9zm0 10v-9h-9v9h9zm-10 10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9z\'/><path d=\'M6 5V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h-1v-9h-9v9h-1v-9h-9v9h-1v-9h-9v9h-1v-9h-9v9h-1v-9h-9v9h-1v-9h-9v9h-1v-9h-9v9h-1v-9h-9v9H0v-1h5v-9H0v-1h5v-9H0v-1h5v-9H0v-1h5v-9H0v-1h5v-9H0v-1h5v-9H0v-1h5v-9H0v-1h5v-9H0V5h6zm10 0h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h9V0h1v5h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h-1v-9h-9v9h-1v-9') }}"); }
            </style>
        @endif
    </head>
    <body class="font-sans text-gray-200 antialiased min-h-screen">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="w-full sm:max-w-md mt-6">
                {{ $slot }}
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const passwordInput = document.getElementById('password');
                const togglePassword = document.getElementById('togglePassword');
                const capsLockWarning = document.getElementById('caps-lock-warning');

                if (togglePassword) {
                    togglePassword.addEventListener('click', function () {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        // Change icon based on password visibility
                        this.querySelector('path:last-child').setAttribute('d', type === 'password' ? 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .946-3.11 3.56-5.604 6.837-6.182m1.5-1.5A12.006 12.006 0 0112 5c4.478 0 8.268 2.943 9.542 7a11.94 11.94 0 01-2.05 3.572m-4.242 4.242A9.95 9.95 0 0112 19c-1.539 0-3.01-.357-4.375-1.002m1.5-1.5l-1.5 1.5m0 0l-1.5-1.5m1.5 1.5V12m0 0l1.5 1.5m-1.5-1.5L12 9m0 0l1.5-1.5M12 9l-1.5-1.5');
                    });
                }

                if (passwordInput) {
                    passwordInput.addEventListener('keyup', function (event) {
                        if (event.getModifierState('CapsLock')) {
                            capsLockWarning.style.display = 'block';
                        } else {
                            capsLockWarning.style.display = 'none';
                        }
                    });
                }

                const passwordConfirmationInput = document.getElementById('password_confirmation');
                const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');

                if (togglePasswordConfirmation) {
                    togglePasswordConfirmation.addEventListener('click', function () {
                        const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordConfirmationInput.setAttribute('type', type);
                        this.querySelector('path:last-child').setAttribute('d', type === 'password' ? 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .946-3.11 3.56-5.604 6.837-6.182m1.5-1.5A12.006 12.006 0 0112 5c4.478 0 8.268 2.943 9.542 7a11.94 11.94 0 01-2.05 3.572m-4.242 4.242A9.95 9.95 0 0112 19c-1.539 0-3.01-.357-4.375-1.002m1.5-1.5l-1.5 1.5m0 0l-1.5-1.5m1.5 1.5V12m0 0l1.5 1.5m-1.5-1.5L12 9m0 0l1.5-1.5M12 9l-1.5-1.5');
                    });
                }
            });
        </script>
    </body>
</html>
