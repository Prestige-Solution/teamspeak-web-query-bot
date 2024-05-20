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
        Schema::create('ts3_channel_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id');
            $table->foreign('server_id')->references('id')->on('ts3_server_configs');
            $table->integer('cgid');
            $table->string('name');
            $table->integer('type');
            $table->bigInteger('iconid');
            $table->integer('savedb');
            $table->integer('sortid');
            $table->integer('namemode');
            $table->integer('n_modifyp');
            $table->integer('n_member_addp');
            $table->integer('n_member_removep');
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
        Schema::dropIfExists('ts3_channel_groups');
    }
};
