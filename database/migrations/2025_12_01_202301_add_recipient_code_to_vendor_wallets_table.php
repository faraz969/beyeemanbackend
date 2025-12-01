<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientCodeToVendorWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_wallets', function (Blueprint $table) {
            $table->string('recipient_code')->nullable()->after('balance');
            $table->string('bank_code')->nullable()->after('bank_name'); // For Ghana banks (ghipss)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_wallets', function (Blueprint $table) {
            $table->dropColumn(['recipient_code', 'bank_code']);
        });
    }
}
