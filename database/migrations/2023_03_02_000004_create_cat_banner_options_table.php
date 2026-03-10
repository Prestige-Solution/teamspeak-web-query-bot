<?php

use Database\Seeders\catBannerOptionSeeder;
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
        Schema::create('cat_banner_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pes_code');
            $table->string('ts3_attribut');
            $table->string('category');
            $table->timestamps();
        });

        $seeder = new catBannerOptionSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_banner_options');
    }
};
