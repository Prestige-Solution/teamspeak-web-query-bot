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
    public function up(): void
    {
        Schema::create('banner_options', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('banner_id');
            $table->integer('font_id');
            $table->integer('font_size');
            $table->string('color_hex');
            $table->bigInteger('option_id');
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
    public function down(): void
    {
        Schema::dropIfExists('banner_options');
    }
};
