<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bad_names', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id');
            $table->string('description');
            $table->integer('value_option')->default(1); // 1 contains / 2 regex
            $table->string('value');
            $table->boolean('failed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bad_names');
    }
};
