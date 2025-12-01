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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'success', 'error'])->default('info');
            $table->enum('recipient_type', ['all', 'vendor', 'customer', 'specific'])->default('all');
            $table->unsignedBigInteger('recipient_id')->nullable(); // For specific user
            $table->unsignedBigInteger('created_by_admin_id');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by_admin_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
