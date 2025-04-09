<?php

namespace App\Models\ts3BotWorkers;

use App\Models\ts3Bot\ts3ServerConfig;
use Database\Factories\UpdateWorkerAfkSettingsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ts3BotWorkerAfk extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'max_client_idle_time',
        'afk_channel_cid',
        'excluded_servergroup',
        'is_afk_active',
        'afk_kicker_max_idle_time',
        'afk_kicker_slots_online',
        'is_afk_kicker_active',
    ];

    protected static function newFactory(): UpdateWorkerAfkSettingsFactory
    {
        return UpdateWorkerAfkSettingsFactory::new();
    }

    public function rel_servers(): HasMany
    {
        return $this->hasMany(ts3ServerConfig::class, 'id', 'server_id');
    }
}
