<?php

namespace App\Models\ts3BotWorkers;

use App\Models\ts3Bot\ts3Channel;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotWorkerChannelRemover extends Model
{
    use HasFactory, Compoships;

    protected $fillable = [
        'server_id',
        'channel_max_seconds_empty',
        'channel_max_time_format',
        'channel_cid',
        'delay',
        'next_check_at',
        'active',
    ];

    public function rel_ts3ChannelsRemover()
    {
        return $this->hasMany(ts3Channel::class,['cid','server_id'],['channel_cid','server_id']);
    }
}
