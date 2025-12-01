<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->integer('quantity_available');
            $table->date('expiry_date')->nullable();
            $table->string('weight')->nullable();
            $table->string('size')->nullable();
            $table->boolean('delivery_enabled')->default(true);
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}

