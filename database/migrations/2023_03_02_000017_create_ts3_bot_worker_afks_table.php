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
        Schema::create('ts3_bot_worker_afks', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id');
            $table->integer('max_client_idle_time')->default(0);
            $table->integer('afk_channel_cid');
            $table->integer('excluded_servergroup')->nullable();
            $table->boolean('is_afk_active')->default(0);
            $table->integer('afk_kicker_max_idle_time')->default(0);
            $table->integer('afk_kicker_slots_online')->default(0);
            $table->boolean('is_afk_kicker_active')->default(0);
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
        Schema::dropIfExists('ts3_bot_worker_afks');
    }
};
