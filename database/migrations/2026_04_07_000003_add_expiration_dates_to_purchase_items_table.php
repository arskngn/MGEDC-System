<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->date('expiration_date')->nullable()->after('line_total');
            $table->date('manufactured_date')->nullable()->after('expiration_date');
            $table->string('batch_number')->nullable()->after('manufactured_date');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['expiration_date', 'manufactured_date', 'batch_number']);
        });
    }
};
