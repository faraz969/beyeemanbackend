<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('shop_name');
            $table->string('shop_logo')->nullable();
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('primary_contact');
            $table->string('alternate_contact')->nullable();
            $table->string('business_email');
            $table->string('business_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('country');
            $table->string('street');
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->enum('status', ['setup', 'active', 'inactive'])->default('setup');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shops');
    }
}

