<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_package_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['momo', 'bank_card', 'wallet']);
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_subscriptions');
    }
}

