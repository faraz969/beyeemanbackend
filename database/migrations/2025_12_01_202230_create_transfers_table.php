<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_wallet_id')->nullable()->constrained('vendor_wallets')->onDelete('set null');
            $table->string('transfer_reference')->unique(); // Generated transfer reference
            $table->string('recipient_code')->nullable(); // Paystack recipient code
            $table->string('transfer_code')->nullable(); // Paystack transfer code
            $table->decimal('amount', 10, 2); // Amount to transfer (after fees)
            $table->decimal('processing_fee', 10, 2)->default(0); // Processing fee deducted
            $table->string('currency', 3)->default('GHS');
            $table->enum('status', ['pending', 'queued', 'success', 'failed', 'reversed'])->default('pending');
            $table->text('reason')->nullable(); // Transfer reason
            $table->text('failure_reason')->nullable(); // If transfer failed
            $table->json('paystack_response')->nullable(); // Full Paystack response
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfers');
    }
}
