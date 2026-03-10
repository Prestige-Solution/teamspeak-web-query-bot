<?php

use Database\Seeders\defaultStatsSeeder;
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
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id');
            $table->integer('virtualserver_server_group_count')->default(0);
            $table->integer('virtualserver_channel_group_count')->default(0);
            $table->integer('virtualserver_banlist_count')->default(0);
            $table->integer('virtualserver_clientsonline')->default(0);
            $table->integer('virtualserver_queryclientsonline')->default(0);
            $table->integer('virtualserver_maxclients')->default(0);
            $table->integer('virtualserver_channelsonline')->default(0);
            $table->string('virtualserver_platform')->nullable();
            $table->string('virtualserver_version')->nullable();
            $table->string('virtualserver_uptime')->nullable();
            $table->string('virtualserver_total_packetloss_keepalive')->nullable();
            $table->integer('virtualserver_total_ping')->default(0);
            $table->string('virtualserver_connection_bytes_received_keepalive')->nullable();
            $table->string('virtualserver_connection_bytes_sent_keepalive')->nullable();
            $table->string('virtualserver_total_packetloss_speech')->nullable();
            $table->integer('virtualserver_reserved_slots')->default(0);
            $table->timestamps();
        });

        $seeder = new defaultStatsSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
