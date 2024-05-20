<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ts3_server_configs',function (Blueprint $table){
            $table->boolean('system_running_before_update')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts3_server_configs',function (Blueprint $table){
            $table->dropColumn('system_running_before_update');
        });
    }
};
