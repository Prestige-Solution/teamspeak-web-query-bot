<?php

namespace App\Models\ts3Bot;

use App\Models\category\catBotStatus;
use App\Models\User;
use Database\Factories\CreateServerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ts3ServerConfig extends Model
{
    use HasFactory;

    public const int BotReconnectTrue = 1;

    public const int BotReconnectFalse = 0;

    public const int TS3ConnectModeSSH = 2;

    protected $fillable = [
        'server_name',
        'server_ip',
        'qa_name',
        'qa_pw',
        'server_query_port',
        'server_port',
        'bot_status_id',
        'description',
        'qa_nickname',
        'is_ts3_start',
        'is_active',
        'mode',
    ];

    public function rel_bot_status(): HasOne
    {
        return $this->hasOne(catBotStatus::class, 'id', 'bot_status_id');
    }

    protected static function newFactory(): CreateServerFactory
    {
        return CreateServerFactory::new();
    }
}
