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
        Schema::create('ts3_server_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('server_name');
            $table->string('ipv4')->unique();
            $table->string('qa_name');
            $table->string('qa_pw');
            $table->integer('server_query_port')->default(10011);
            $table->integer('server_port')->default(9987);
            $table->foreignId('bot_status_id')->default(3);
            $table->foreign('bot_status_id')->references('id')->on('cat_bot_statuses');
            $table->string('description')->nullable();
            $table->string('qa_nickname')->nullable();
            $table->boolean('ts3_start_stop')->default(0);
            $table->string('bot_confirm_token',13)->nullable();
            $table->boolean('bot_confirmed')->default(0);
            $table->timestamp('bot_confirmed_at')->nullable();
            $table->integer('plan_level')->default(1);
            $table->boolean('active')->default(1);
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
        Schema::dropIfExists('ts3_server_configs');
    }
};
