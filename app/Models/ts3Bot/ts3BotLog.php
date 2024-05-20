<?php

namespace App\Models\ts3Bot;

use App\Models\category\catBotStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotLog extends Model
{
    use HasFactory;

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
        return $this->hasMany(catBotStatus::class,'id','status_id');
    }
}
