<?php

namespace App\Models\sys;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class system_config extends Model
{
    use HasFactory;

    protected $fillable = [
        'seed_version',
    ];
}
