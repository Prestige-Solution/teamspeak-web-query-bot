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
        Schema::create('ts_server_configs', function (Blueprint $table) {
            $table->id();
            $table->string('server_name');
            $table->string('server_ip');
            $table->string('qa_name');
            $table->string('qa_pw', 2048);
            $table->integer('server_query_port')->nullable();
            $table->integer('server_port')->default(9987);
            $table->integer('bot_status_id')->default(3);
            $table->string('description')->nullable();
            $table->string('qa_nickname')->nullable();
            $table->boolean('is_ts_start')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('mode')->default(1);
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
        Schema::dropIfExists('ts_server_configs');
    }
};
