<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryZonesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('location_name');
            $table->decimal('delivery_fee', 10, 2);
            $table->integer('estimated_delivery_time'); // in minutes
            $table->enum('delivery_type', ['vendor', 'platform'])->default('vendor');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_zones');
    }
}

