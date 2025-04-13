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
        Schema::create('ts3_user_databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id');
            $table->foreign('server_id')->references('id')->on('ts3_server_configs');
            $table->string('client_unique_identifier');
            $table->string('client_nickname');
            $table->integer('client_database_id');
            $table->timestamp('client_created')->nullable();
            $table->timestamp('client_lastconnected')->nullable();
            $table->integer('client_totalconnections')->default(0);
            $table->text('client_flag_avatar')->nullable();
            $table->text('client_description')->nullable();
            $table->bigInteger('client_month_bytes_uploaded');
            $table->bigInteger('client_month_bytes_downloaded');
            $table->bigInteger('client_total_bytes_uploaded');
            $table->bigInteger('client_total_bytes_downloaded');
            $table->string('client_base64HashClientUID')->nullable();
            $table->string('client_lastip')->nullable();
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
        Schema::dropIfExists('ts3_user_databases');
    }
};
