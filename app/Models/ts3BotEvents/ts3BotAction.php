<?php

namespace App\Models\ts3BotEvents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotAction extends Model
{
    protected $fillable = [
        'type_id',
        'action_bot',
        'action_name',
    ];
}
