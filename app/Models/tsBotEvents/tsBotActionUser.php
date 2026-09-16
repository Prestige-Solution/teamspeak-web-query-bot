<?php

namespace App\Models\tsBotEvents;

use Illuminate\Database\Eloquent\Model;

class tsBotActionUser extends Model
{
    protected $fillable = [
        'type_id',
        'action_bot',
        'action_name',
    ];
}
