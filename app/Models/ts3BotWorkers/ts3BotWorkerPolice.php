<?php

namespace App\Models\ts3BotWorkers;

use Database\Factories\CreateWorkerPoliceSettingsFactory;
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
        'is_bad_name_protection_active',
        'is_bad_name_protection_global_list_active',
    ];

    protected static function newFactory(): CreateWorkerPoliceSettingsFactory
    {
        return CreateWorkerPoliceSettingsFactory::new();
    }

}
