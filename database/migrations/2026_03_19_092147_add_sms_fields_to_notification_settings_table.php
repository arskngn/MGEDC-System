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
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->string('sms_sent_from')->nullable()->after('email_body');
            $table->text('sms_body')->nullable()->after('sms_sent_from');
        });

        // Seed some initial data for SMS
        \Illuminate\Support\Facades\DB::table('notification_settings')->where('id', 1)->update([
            'sms_sent_from' => 'MGEDC_ADMIN',
            'sms_body' => 'Hello {{fullname}}, {{message}}',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn(['sms_sent_from', 'sms_body']);
        });
    }
};
