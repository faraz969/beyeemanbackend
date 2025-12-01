<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailabilityRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('availability_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('requested_quantity');
            $table->enum('status', ['pending', 'available', 'out_of_stock', 'limited'])->default('pending');
            $table->integer('available_quantity')->nullable(); // For limited stock
            $table->text('vendor_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('availability_requests');
    }
}

