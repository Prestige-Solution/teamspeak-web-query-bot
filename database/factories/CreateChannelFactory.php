<?php

namespace Database\Factories;

use App\Models\ts3Bot\ts3Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreateChannelFactory extends Factory
{
    protected $model = ts3Channel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id'=>1,
            'cid'=>54,
            'pid'=>0,
            'channel_order'=>0,
            'channel_name'=>'UnitTest',
            'channel_topic'=>'',
            'channel_flag_default'=>0,
            'channel_flag_password'=>0,
            'channel_flag_permanent'=>1,
            'channel_flag_semi_permanent'=>0,
            'channel_codec'=>4,
            'channel_codec_quality'=>6,
            'channel_needed_talk_power'=>0,
            'channel_icon_id'=>0,
            'total_clients_family'=>0,
            'channel_maxclients'=>-1,
            'channel_maxfamilyclients'=>-1,
            'total_clients'=>0,
            'channel_needed_subscribe_power'=>0,
            'channel_banner_gfx_url'=>0,
            'channel_banner_mode'=>0,
            'channel_description'=>null,
            'channel_password'=>0,
            'channel_codec_latency_factor'=>1,
            'channel_codec_is_unencrypted'=>1,
            'channel_security_salt'=>0,
            'channel_delete_delay'=>0,
            'channel_unique_identifier'=>'1e9ff5ad-163f-4440-b93b-44c4f85b3f2e',
            'channel_flag_maxclients_unlimited'=>1,
            'channel_flag_maxfamilyclients_unlimited'=>0,
            'channel_flag_maxfamilyclients_inherited'=>1,
            'channel_filepath'=>'files/virtualserver_1/channel_54',
            'channel_forced_silence'=>0,
            'channel_name_phonetic'=>0,
            'seconds_empty'=>259329,
        ];
    }
}
