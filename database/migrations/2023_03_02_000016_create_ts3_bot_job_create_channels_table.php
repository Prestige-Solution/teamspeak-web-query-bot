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
        Schema::create('ts3_bot_job_create_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id');
            $table->foreign('server_id')->references('id')->on('ts3_server_configs');
            $table->foreignId('type_id');
            $table->foreign('type_id')->references('id')->on('cat_bot_job_types');
            $table->integer('on_cid')->nullable();
            $table->string('on_event')->nullable();
            $table->integer('action_id')->nullable();
            $table->integer('action_min_clients')->default(1);
            $table->integer('create_max_channels')->default(10);
            $table->integer('action_user_id')->nullable();
            $table->integer('channel_cgid')->nullable();
            $table->integer('channel_template_id')->nullable();
            $table->boolean('notify_message_server_group')->default(0);
            $table->integer('notify_message_server_group_sgid')->nullable();
            $table->string('notify_message_server_group_message')->nullable();
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
        Schema::dropIfExists('ts3_bot_job_create_channels');
    }
};
