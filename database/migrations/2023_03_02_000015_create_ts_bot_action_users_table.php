<?php

use Database\Seeders\tsActionUserSeeder;
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
        Schema::create('ts_bot_action_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('type_id');
            $table->string('action_bot');
            $table->string('action_name');
            $table->timestamps();
        });

        $seeder = new tsActionUserSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_bot_action_users');
    }
};
