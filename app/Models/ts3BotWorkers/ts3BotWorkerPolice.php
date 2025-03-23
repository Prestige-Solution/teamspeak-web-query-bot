<?php

namespace App\Models\ts3BotWorkers;

use Database\Factories\CreateServerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotWorkerPolice extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'is_discord_webhook_active',
        'is_check_bot_alive_active',
        'is_vpn_protection_active',
        'vpn_protection_api_register_mail',
        'vpn_protection_query_count',
        'vpn_protection_query_max',
        'vpn_protection_next_check_available_at',
        'discord_webhook_url',
        'allow_sgid_vpn',
        'client_forget_offline_time',
        'client_forget_time_type',
        'client_forget_after_at',
        'is_client_forget_active',
        'is_bad_name_protection_active',
        'is_bad_name_protection_global_list_active',
    ];

    protected static function newFactory(): CreateServerFactory
    {
        return CreateServerFactory::new();
    }
}
