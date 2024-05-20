<?php

namespace App\Models\sys;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class badName extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'description',
        'value_option',
        'value',
        'failed',
    ];
}
