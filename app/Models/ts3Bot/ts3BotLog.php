<?php

namespace App\Models\ts3Bot;

use App\Models\category\catBotStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotLog extends Model
{
    use HasFactory;

    public const RUNNING = 1;

    public const TRY_RECONNECT = 2;

    public const STOPPED = 3;

    public const FAILED = 4;

    public const SUCCESS = 5;

    protected $fillable = [
        'server_id',
        'status_id',
        'job',
        'description',
        'error_code',
        'error_message',
        'worker',
    ];

    public function rel_bot_status()
    {
        return $this->hasMany(catBotStatus::class, 'id', 'status_id');
    }
}
