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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('raised_by_user_id'); // Customer or Vendor who raised the dispute
            $table->enum('raised_by_type', ['customer', 'vendor']);
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['pending', 'under_review', 'resolved', 'closed'])->default('pending');
            $table->enum('resolved_in_favor_of', ['customer', 'vendor', null])->nullable();
            $table->text('admin_remarks')->nullable();
            $table->unsignedBigInteger('resolved_by_admin_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('raised_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('resolved_by_admin_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['order_id', 'status']);
            $table->index('raised_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
