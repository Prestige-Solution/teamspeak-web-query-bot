<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id');
            $table->foreign('banner_id')->references('id')->on('banners');
            $table->integer('font_id');
            $table->integer('font_size');
            $table->string('color_hex');
            $table->foreignId('option_id');
            $table->foreign('option_id')->references('id')->on('cat_banner_options');
            $table->integer('extra_option')->nullable();
            $table->string('text')->nullable();
            $table->integer('coord_x')->nullable();
            $table->integer('coord_y')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banner_options');
    }
};
