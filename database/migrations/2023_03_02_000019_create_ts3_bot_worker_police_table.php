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
    public function up(): void
    {
        Schema::create('ts3_bot_worker_police', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id');
            $table->foreign('server_id')->references('id')->on('ts3_server_configs');
            $table->boolean('is_discord_webhook_active')->default(false);
            $table->boolean('is_check_bot_alive_active')->default(false);
            $table->string('discord_webhook_url', 2048)->nullable();
            $table->boolean('is_vpn_protection_active')->default(false);
            $table->integer('allow_sgid_vpn')->default(1);
            $table->integer('vpn_protection_query_count')->default(0);
            $table->integer('vpn_protection_query_max')->default(15);
            $table->integer('vpn_protection_query_per_day')->default(0);
            $table->integer('vpn_protection_max_query_per_day')->default(500);
            $table->integer('vpn_protection_api_register_mail')->nullable();
            $table->timestamp('vpn_protection_next_check_available_at')->default(Carbon::now());
            $table->boolean('is_channel_auto_update_active')->default(false);
            $table->integer('client_forget_offline_time')->default(8);
            $table->integer('client_forget_time_type')->default(1);
            $table->timestamp('client_forget_after_at')->default(Carbon::now()->addWeekdays(8));
            $table->boolean('is_client_forget_active')->default(false);
            $table->boolean('is_bad_name_protection_active')->default(false);
            $table->boolean('is_bad_name_protection_global_list_active')->default(false);
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
        Schema::dropIfExists('ts3_bot_police_workers');
    }
};
