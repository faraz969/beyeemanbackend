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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('currency', 3)->nullable()->after('category'); // ISO 4217 currency code (e.g., USD, GHS, EUR)
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('currency'); // Exchange rate to base currency
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });
    }
};
