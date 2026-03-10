<?php

use Database\Seeders\ts3eventSeeder;
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
        Schema::create('ts3_bot_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_ts');
            $table->string('event_name');
            $table->string('event_description');
            $table->integer('cat_job_type');
            $table->timestamps();
        });

        $seeder = new ts3eventSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ts3_bot_events');
    }
};
