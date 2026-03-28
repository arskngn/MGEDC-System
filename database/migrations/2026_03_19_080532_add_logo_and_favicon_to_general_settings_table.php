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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('logo_light')->nullable()->after('currency_format');
            $table->string('logo_dark')->nullable()->after('logo_light');
            $table->string('favicon')->nullable()->after('logo_dark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_light', 'logo_dark', 'favicon']);
        });
    }
};
