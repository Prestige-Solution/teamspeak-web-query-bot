<?php

namespace App\Models\tsBot;

use App\Models\category\catBotStatus;
use Illuminate\Database\Eloquent\Model;

class tsBotLog extends Model
{
    public const int RUNNING = 1;

    public const int TRY_RECONNECT = 2;

    public const int SHUTDOWN = 3;

    public const int FAILED = 4;

    public const int SUCCESS = 5;

    protected $fillable = [
        'server_id',
        'status_id',
        'job',
        'description',
        'error_code',
        'error_message',
        'worker',
    ];

    public function rel_bot_status(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(catBotStatus::class, 'id', 'status_id');
    }
}
