<?php

namespace App\Models\tsBotWorkers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tsBotWorkerPoliceVpnProtection extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'ip_address',
        'check_result',
    ];
}
