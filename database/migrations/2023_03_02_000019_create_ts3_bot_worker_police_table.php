<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
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
        Schema::create('ts3_bot_worker_police', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id');
            $table->foreign('server_id')->references('id')->on('ts3_server_configs');
            $table->boolean('discord_webhook_active')->default(0);
            $table->boolean('check_bot_alive_active')->default(0);
            $table->string('discord_webhook',2048)->nullable();
            $table->boolean('vpn_protection_active')->default(0);
            $table->integer('allow_sgid_vpn')->default(1);
            $table->integer('vpn_protection_query_count')->default(0);
            $table->integer('vpn_protection_query_max')->default(15);
            $table->integer('vpn_protection_query_per_day')->default(0);
            $table->timestamp('vpn_protection_next_check_available')->default(Carbon::now());
            $table->boolean('channel_auto_update')->default(0);
            $table->integer('client_forget_offline_time')->default(8);
            $table->integer('client_forget_type')->default(1);
            $table->timestamp('client_forget_after')->default(Carbon::now()->addWeekdays(8));
            $table->boolean('client_forget_active')->default(0);
            $table->boolean('bad_name_protection_active')->default(0);
            $table->boolean('bad_name_protection_global_list_active')->default(0);
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
        Schema::dropIfExists('ts3_bot_police_workers');
    }
};
