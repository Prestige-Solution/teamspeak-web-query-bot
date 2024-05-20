<?php

namespace App\Models\sys;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invite extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'invited_by',
        'email',
        'invite_code',
        'expire_at',
        'invite_accepted',
    ];
}
