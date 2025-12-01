<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturedBannersTable extends Migration
{
    public function up()
    {
        Schema::create('featured_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image');
            $table->string('link_type')->nullable(); // product, vendor, category, url
            $table->unsignedBigInteger('link_id')->nullable();
            $table->string('external_url')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('featured_banners');
    }
}

