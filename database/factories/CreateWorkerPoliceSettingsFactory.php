<?php

namespace Database\Factories;

use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class CreateWorkerPoliceSettingsFactory extends Factory
{
    protected $model = ts3BotWorkerPolice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id'=>1,
            'is_discord_webhook_active'=>false,
            'is_check_bot_alive_active'=>false,
            'is_vpn_protection_active'=>false,
            'discord_webhook_url'=>Crypt::encryptString('test-url'),
            'allow_sgid_vpn'=>0,
            'is_channel_auto_update_active'=>false,
            'is_bad_name_protection_active'=>false,
            'is_bad_name_protection_global_list_active'=>false,
        ];
    }
}
