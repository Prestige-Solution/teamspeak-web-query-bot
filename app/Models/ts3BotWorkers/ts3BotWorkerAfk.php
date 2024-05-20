<?php

namespace App\Models\ts3BotWorkers;

use App\Http\Controllers\ts3Config\Ts3ConfigController;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotWorkerAfk extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'max_client_idle_time',
        'afk_channel_cid',
        'excluded_servergroup',
        'active',
        'afk_kicker_max_idle_time',
        'afk_kicker_slots_online',
        'afk_kicker_active',
    ];

    public function rel_servers()
    {
        return $this->hasMany(ts3ServerConfig::class,'id','server_id');
    }
}
