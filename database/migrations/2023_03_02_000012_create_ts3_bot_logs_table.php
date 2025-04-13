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
        Schema::create('ts3_bot_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id');
            $table->foreignId('status_id');
            $table->foreign('status_id')->references('id')->on('cat_bot_statuses');
            $table->string('job');
            $table->string('description')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->string('worker');
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
        Schema::dropIfExists('ts3_bot_logs');
    }
};
