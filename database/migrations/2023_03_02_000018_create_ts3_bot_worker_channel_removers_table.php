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
        Schema::create('ts3_bot_worker_channel_removers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id');
            $table->foreign('server_id')->references('id')->on('ts3_server_configs');
            $table->integer('channel_max_seconds_empty')->default(0);
            $table->string('channel_max_time_format',1)->default('d');
            $table->integer('channel_cid');
            $table->integer('delay')->default(0);
            $table->timestamp('next_check_at')->nullable();
            $table->boolean('active')->default(0);
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
        Schema::dropIfExists('ts3_bot_worker_channel_removers');
    }
};
