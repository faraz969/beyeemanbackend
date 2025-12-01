<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPackagesTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic, Standard, Premium, Market Day
            $table->string('duration_type'); // month, months, year, days
            $table->integer('duration_value'); // 1, 3, 12, 1, 2, 5
            $table->integer('max_products')->nullable(); // null for unlimited
            $table->decimal('price', 10, 2);
            $table->text('features')->nullable(); // JSON or text
            $table->boolean('featured_listing')->default(false);
            $table->integer('featured_listing_count')->default(0);
            $table->boolean('priority_visibility')->default(false);
            $table->boolean('free_promotions')->default(false);
            $table->boolean('dashboard_analytics')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_packages');
    }
}

