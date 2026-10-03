<?php

namespace App\Models\tsBotEvents;

use Illuminate\Database\Eloquent\Model;

class tsBotEvent extends Model
{
    protected $fillable = [
        'event_ts',
        'event_name',
        'event_description',
        'cat_job_type',
    ];
}
