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
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->string('email_sent_from_name')->nullable();
            $table->string('email_sent_from_email')->nullable();
            $table->string('sms_sent_from')->nullable();
            $table->string('push_title')->nullable();
            $table->text('push_body')->nullable();
            $table->boolean('is_push_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn([
                'email_sent_from_name',
                'email_sent_from_email',
                'sms_sent_from',
                'push_title',
                'push_body',
                'is_push_enabled'
            ]);
        });
    }
};
