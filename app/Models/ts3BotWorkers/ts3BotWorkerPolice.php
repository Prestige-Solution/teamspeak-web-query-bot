<?php

namespace App\Models\ts3BotWorkers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotWorkerPolice extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'discord_webhook_active',
        'check_bot_alive_active',
        'vpn_protection_active',
        'vpn_protection_query_count',
        'vpn_protection_query_max',
        'vpn_protection_next_check_available',
        'discord_webhook',
        'allow_sgid_vpn',
        'allow_record',
        'client_forget_offline_time',
        'client_forget_type',
        'client_forget_after',
        'client_forget_active',
        'bad_name_protection_active',
        'bade_name_protection_global_list_active',
    ];
}
