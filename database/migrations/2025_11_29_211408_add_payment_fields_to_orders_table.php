<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Payment fields
            $table->string('paystack_reference')->nullable()->after('payment_method');
            $table->decimal('processing_fee', 10, 2)->default(0)->after('paystack_reference');
            $table->decimal('platform_fee', 10, 2)->default(0)->after('processing_fee');
            $table->decimal('subtotal', 10, 2)->default(0)->after('platform_fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'paystack_reference',
                'processing_fee',
                'platform_fee',
                'subtotal',
            ]);
        });
    }
}
