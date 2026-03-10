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
        Schema::create('ts3_bot_worker_police_vpn_protections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('server_id');
            $table->ipAddress();
            $table->string('check_result');
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
        Schema::dropIfExists('ts3_bot_worker_police_vpn_protections');
    }
};
