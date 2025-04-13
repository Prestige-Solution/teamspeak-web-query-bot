<?php

namespace Database\Factories;

use App\Models\ts3BotWorkers\ts3BotWorkerChannelsCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreateJobChannelCreatorFactory extends Factory
{
    protected $model = ts3BotWorkerChannelsCreate::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id'=>1,
            'type_id'=>1,
            'on_cid'=>54,
            'on_event'=>"clientmoved",
            'action_id'=>3,
            'action_min_clients'=>1,
            'create_max_channels'=>10,
            'action_user_id'=>2,
            'channel_cgid'=>9,
            'channel_template_id'=>0,
            'is_notify_message_server_group'=>1,
            'notify_message_server_group_sgid'=>27,
            'notify_message_server_group_message'=>'UnitTest',
            'is_active'=>true,
        ];
    }
}
