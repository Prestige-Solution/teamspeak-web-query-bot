<?php

use Database\Seeders\tsActionSeeder;
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
        Schema::create('ts_bot_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('type_id');
            $table->string('action_bot');
            $table->string('action_name');
            $table->timestamps();
        });

        $seeder = new tsActionSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_bot_actions');
    }
};
