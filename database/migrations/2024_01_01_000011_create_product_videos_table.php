<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVideosTable extends Migration
{
    public function up()
    {
        Schema::create('product_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('video_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_videos');
    }
}

