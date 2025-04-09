<?php

namespace Database\Factories;

use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use Illuminate\Database\Eloquent\Factories\Factory;

class UpdateWorkerAfkSettingsFactory extends Factory
{
    protected $model = ts3botworkerafk::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id'=>1,
            'is_afk_active'=>false,
            'max_client_idle_time'=>1,
            'afk_channel_cid'=>54,
            'is_afk_kicker_active'=>false,
            'afk_kicker_max_idle_time'=>1,
            'afk_kicker_slots_online'=>10,
            'excluded_servergroup'=>[0=>"27"],
        ];
    }
}
