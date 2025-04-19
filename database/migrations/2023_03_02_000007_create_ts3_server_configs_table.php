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
        Schema::create('ts3_server_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('server_name');
            $table->string('server_ip')->unique();
            $table->string('qa_name');
            $table->string('qa_pw', 2048);
            $table->integer('server_query_port')->nullable();
            $table->integer('server_port')->default(9987);
            $table->foreignId('bot_status_id')->default(3);
            $table->foreign('bot_status_id')->references('id')->on('cat_bot_statuses');
            $table->string('description')->nullable();
            $table->string('qa_nickname')->nullable();
            $table->boolean('is_ts3_start')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(true);
            $table->integer('mode')->default(1);
            $table->boolean('is_bot_update')->default(false);
            $table->boolean('is_system_running_before_update')->default(false);
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
        Schema::dropIfExists('ts3_server_configs');
    }
};
