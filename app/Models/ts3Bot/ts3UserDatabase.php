<?php

namespace App\Models\ts3Bot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3UserDatabase extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'client_unique_identifier',
        'client_nickname',
        'client_database_id',
        'client_created',
        'client_lastconnected',
        'client_totalconnections',
        'client_flag_avatar',
        'client_description',
        'client_month_bytes_uploaded',
        'client_month_bytes_downloaded',
        'client_total_bytes_uploaded',
        'client_total_bytes_downloaded',
        'client_base64HashClientUID',
        'client_lastip',
    ];
}
