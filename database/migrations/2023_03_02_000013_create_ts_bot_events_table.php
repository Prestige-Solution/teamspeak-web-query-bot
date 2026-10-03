<?php

use Database\Seeders\tsEventSeeder;
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
        Schema::create('ts_bot_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_ts');
            $table->string('event_name');
            $table->string('event_description');
            $table->integer('cat_job_type');
            $table->timestamps();
        });

        $seeder = new tsEventSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_bot_events');
    }
};
