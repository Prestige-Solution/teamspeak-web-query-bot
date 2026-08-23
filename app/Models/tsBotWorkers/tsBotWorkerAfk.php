<?php

namespace App\Models\tsBotWorkers;

use App\Models\tsBot\tsServerConfig;
use Database\Factories\UpdateWorkerAfkSettingsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class tsBotWorkerAfk extends Model
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
        return $this->hasMany(tsServerConfig::class, 'id', 'server_id');
    }
}
