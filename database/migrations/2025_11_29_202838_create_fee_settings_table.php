<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_settings', function (Blueprint $table) {
            $table->id();
            
            // Processing Fee
            $table->enum('processing_fee_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('processing_fee_value', 10, 2)->default(0);
            
            // Platform Fee
            $table->enum('platform_fee_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('platform_fee_value', 10, 2)->default(0);
            
            // Applicable to (for future use - customer, vendor, or both)
            $table->string('processing_fee_applicable_to', 50)->default('customer'); // customer, vendor, both
            $table->string('platform_fee_applicable_to', 50)->default('vendor'); // customer, vendor, both
            
            $table->timestamps();
        });
        
        // Create a default fee setting record
        \DB::table('fee_settings')->insert([
            'processing_fee_type' => 'percentage',
            'processing_fee_value' => 2.5, // 2.5%
            'platform_fee_type' => 'percentage',
            'platform_fee_value' => 5.0, // 5%
            'processing_fee_applicable_to' => 'customer',
            'platform_fee_applicable_to' => 'vendor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_settings');
    }
}
