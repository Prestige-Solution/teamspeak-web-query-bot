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
        Schema::create('ts3_server_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('server_id');
            $table->integer('sgid');
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
    public function down(): void
    {
        Schema::dropIfExists('ts3_server_groups');
    }
};
