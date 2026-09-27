<?php

namespace App\Models\tsBotWorkers;

use Illuminate\Database\Eloquent\Model;

class tsBotWorkerPoliceVpnProtection extends Model
{
    protected $fillable = [
        'server_id',
        'ip_address',
        'check_result',
    ];
}
