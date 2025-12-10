<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // e.g., 'vendor.created', 'product.updated', 'order.status_changed'
            $table->string('model_type')->nullable(); // e.g., 'App\Models\Vendor', 'App\Models\Product'
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected model
            $table->string('user_type')->nullable(); // 'admin', 'vendor', 'customer', 'system'
            $table->unsignedBigInteger('user_id')->nullable(); // ID of the user who performed the action
            $table->string('description'); // Human-readable description
            $table->text('old_values')->nullable(); // JSON of old values (for updates)
            $table->text('new_values')->nullable(); // JSON of new values (for updates)
            $table->text('metadata')->nullable(); // Additional JSON data
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['model_type', 'model_id']);
            $table->index(['user_type', 'user_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
}
