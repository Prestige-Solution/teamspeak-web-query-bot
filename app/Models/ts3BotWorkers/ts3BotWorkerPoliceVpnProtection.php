<?php

namespace App\Models\ts3BotWorkers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotWorkerPoliceVpnProtection extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'ip_address',
        'check_result',
    ];
}
