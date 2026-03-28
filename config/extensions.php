<?php

return [
    'custom-captcha' => [
        'name' => 'Custom Captcha',
        'description' => 'A custom captcha extension for forms.',
        'image' => 'system-images/captcha.png',
        'shortcode' => ['site_key' => '', 'secret_key' => ''],
        'help_text' => 'Enter your custom captcha site key and secret key.',
    ],
    'google-recaptcha-2' => [
        'name' => 'Google Recaptcha 2',
        'description' => 'Google Recaptcha 2 extension for forms.',
        'image' => 'system-images/recaptcha.png',
        'shortcode' => ['site_key' => '', 'secret_key' => ''],
        'help_text' => 'Enter your Google Recaptcha 2 site key and secret key.',
    ],
];
