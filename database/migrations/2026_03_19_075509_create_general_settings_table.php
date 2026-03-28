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
        Schema::create('general_settings', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('site_title')->default('MGEDC');
            $blueprint->string('currency')->default('USD');
            $blueprint->string('currency_symbol')->default('$');
            $blueprint->string('timezone')->default('UTC');
            $blueprint->integer('records_per_page')->default(20);
            $blueprint->string('currency_format')->default('both'); // both, text, symbol
            $blueprint->timestamps();
        });

        // Insert default settings
        \Illuminate\Support\Facades\DB::table('general_settings')->insert([
            'site_title' => 'MGEDC',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'timezone' => 'Asia/Manila',
            'records_per_page' => 15,
            'currency_format' => 'both',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
