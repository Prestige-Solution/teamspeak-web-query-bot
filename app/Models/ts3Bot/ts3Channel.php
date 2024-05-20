<?php

namespace App\Models\ts3Bot;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3Channel extends Model
{
    use HasFactory, Compoships;

    protected $fillable = [
        'server_id',
        'cid',
        'pid',
        'channel_order',
        'channel_name',
        'channel_topic',
        'channel_flag_default',
        'channel_flag_password',
        'channel_flag_permanent',
        'channel_flag_semi_permanent',
        'channel_codec',
        'channel_codec_quality',
        'channel_needed_talk_power',
        'channel_icon_id',
        'total_clients_family',
        'channel_maxclients',
        'channel_maxfamilyclients',
        'total_clients',
        'channel_needed_subscribe_power',
        'channel_banner_gfx_url',
        'channel_banner_mode',
        'channel_description',
        'channel_password',
        'channel_codec_latency_factor',
        'channel_codec_is_unencrypted',
        'channel_security_salt',
        'channel_delete_delay',
        'channel_unique_identifier',
        'channel_flag_maxclients_unlimited',
        'channel_flag_maxfamilyclients_unlimited',
        'channel_flag_maxfamilyclients_inherited',
        'channel_filepath',
        'channel_forced_silence',
        'channel_name_phonetic',
        'seconds_empty',
    ];
}
