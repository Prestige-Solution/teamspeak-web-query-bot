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
    public function up()
    {
        Schema::create('ts3_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id');
            $table->foreign('server_id')->references('id')->on('ts3_server_configs');
            $table->integer('cid');
            $table->integer('pid');
            $table->integer('channel_order');
            $table->string('channel_name');
            $table->string('channel_topic')->nullable();
            $table->integer('channel_flag_default');
            $table->integer('channel_flag_password');
            $table->integer('channel_flag_permanent');
            $table->integer('channel_flag_semi_permanent');
            $table->integer('channel_codec');
            $table->integer('channel_codec_quality');
            $table->integer('channel_needed_talk_power');
            $table->bigInteger('channel_icon_id');
            $table->integer('total_clients_family');
            $table->integer('channel_maxclients');
            $table->integer('channel_maxfamilyclients');
            $table->integer('total_clients');
            $table->integer('channel_needed_subscribe_power');
            $table->string('channel_banner_gfx_url',2048)->nullable();
            $table->integer('channel_banner_mode');
            $table->text('channel_description')->nullable();
            $table->string('channel_password')->nullable();
            $table->integer('channel_codec_latency_factor');
            $table->integer('channel_codec_is_unencrypted');
            $table->string('channel_security_salt')->nullable();
            $table->integer('channel_delete_delay');
            $table->string('channel_unique_identifier');
            $table->integer('channel_flag_maxclients_unlimited');
            $table->integer('channel_flag_maxfamilyclients_unlimited');
            $table->integer('channel_flag_maxfamilyclients_inherited');
            $table->string('channel_filepath',2048);
            $table->integer('channel_forced_silence');
            $table->string('channel_name_phonetic')->nullable();
            $table->integer('seconds_empty');
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
        Schema::dropIfExists('ts3_channels');
    }
};
