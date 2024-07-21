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
        Schema::table('ts3_server_configs', function (Blueprint $table) {
            $table->boolean('default')->default(false);
        });

        Schema::table('ts3_server_configs', function (Blueprint $table) {
            $table->integer('mode')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts3_server_configs', function (Blueprint $table) {
            $table->dropColumn('default');
        });

        Schema::table('ts3_server_configs', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
