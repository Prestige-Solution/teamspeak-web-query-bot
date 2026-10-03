<?php

namespace App\Models\tsBotWorkers;

use App\Models\tsBot\tsChannel;
use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\HasMany;
use Database\Factories\CreateJobChannelRemoverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tsBotWorkerChannelsRemove extends Model
{
    use HasFactory, Compoships;

    protected $fillable = [
        'server_id',
        'channel_max_seconds_empty',
        'channel_max_time_format',
        'channel_cid',
        'delay',
        'next_check_at',
        'is_active',
    ];

    protected static function newFactory(): CreateJobChannelRemoverFactory
    {
        return CreateJobChannelRemoverFactory::new();
    }

    public function rel_channels(): HasMany
    {
        return $this->hasMany(tsChannel::class, ['cid', 'server_id'], ['channel_cid', 'server_id']);
    }
}
