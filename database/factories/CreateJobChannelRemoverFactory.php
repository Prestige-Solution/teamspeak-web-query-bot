<?php

namespace Database\Factories;

use App\Models\tsBotWorkers\tsBotWorkerChannelsRemove;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreateJobChannelRemoverFactory extends Factory
{
    protected $model = tsBotWorkerChannelsRemove::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id'=>1,
            'channel_cid'=>54,
            'channel_max_seconds_empty'=> 60,
            'channel_max_time_format'=>'m',
            'is_active'=>true,
        ];
    }
}
