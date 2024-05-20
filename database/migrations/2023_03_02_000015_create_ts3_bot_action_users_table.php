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
        Schema::create('ts3_bot_action_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id');
            $table->foreign('type_id')->references('id')->on('cat_bot_job_types');
            $table->string('action_bot');
            $table->string('action_name');
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
        Schema::dropIfExists('ts3_bot_action_users');
    }
};
