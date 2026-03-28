<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject')->nullable();
            $table->text('email_body')->nullable();
            $table->text('sms_body')->nullable();
            $table->boolean('is_email_enabled')->default(true);
            $table->boolean('is_sms_enabled')->default(true);
            $table->json('shortcodes')->nullable();
            $table->timestamps();
        });

        // Seed default templates
        $templates = [
            [
                'name' => 'Password Reset',
                'slug' => 'password_reset',
                'subject' => 'Password Reset Notification',
                'email_body' => 'Hello {{fullname}}, your password reset code is {{code}}.',
                'sms_body' => 'Hello {{fullname}}, your password reset code is {{code}}.',
                'shortcodes' => json_encode(['{{fullname}}' => 'Full Name', '{{code}}' => 'Reset Code']),
            ],
            [
                'name' => 'New User Registration',
                'slug' => 'registration',
                'subject' => 'Welcome to MGEDC',
                'email_body' => 'Hello {{fullname}}, welcome to our system! Your username is {{username}}.',
                'sms_body' => 'Hello {{fullname}}, welcome to MGEDC! Your username is {{username}}.',
                'shortcodes' => json_encode(['{{fullname}}' => 'Full Name', '{{username}}' => 'Username']),
            ]
        ];

        foreach ($templates as $template) {
            \App\Models\NotificationTemplate::create($template);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
