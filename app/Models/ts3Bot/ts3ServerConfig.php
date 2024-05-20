<?php

namespace App\Models\ts3Bot;

use App\Models\category\catBotStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;

class ts3ServerConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'server_name',
        'ipv4',
        'qa_name',
        'qa_pw',
        'server_query_port',
        'server_port',
        'bot_status_id',
        'description',
        'qa_nickname',
        'ts3_start_stop',
        'bot_confirmed',
        'bot_confirm_token',
        'bot_confirmed_at',
        'active',
    ];

    /**
     * @return HasOne
     */
    public function rel_bot_status(): HasOne
    {
        return $this->hasOne(catBotStatus::class,'id','bot_status_id');
    }

    public function rel_ts3serverConfig()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

}
