<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorWalletsTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->enum('payment_type', ['mobile_money', 'bank_account', 'internal_wallet']);
            $table->string('provider')->nullable(); // MTN, Vodafone, AirtelTigo
            $table->string('momo_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('branch')->nullable();
            $table->decimal('balance', 15, 2)->default(0); // For internal wallet
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_wallets');
    }
}

