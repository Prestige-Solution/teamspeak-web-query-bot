<?php

namespace App\Models\ts3BotEvents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotEvent extends Model
{
    protected $fillable = [
        'event_ts',
        'event_name',
        'event_description',
        'cat_job_type',
    ];
}
