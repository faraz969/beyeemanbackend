<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_address_id')->nullable()->constrained('customer_addresses')->onDelete('set null');
            $table->string('payment_method')->nullable();
            $table->enum('delivery_status', ['pending', 'processing', 'ready', 'out_for_delivery', 'delivered'])->default('pending');
            $table->text('customer_notes')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->boolean('availability_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_address_id']);
            $table->dropColumn([
                'delivery_address_id',
                'payment_method',
                'delivery_status',
                'customer_notes',
                'vendor_notes',
                'coupon_code',
                'coupon_discount',
                'availability_confirmed',
                'confirmed_at',
            ]);
        });
    }
}

