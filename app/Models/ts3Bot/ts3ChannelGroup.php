<?php

namespace App\Models\ts3Bot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3ChannelGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'cgid',
        'name',
        'type',
        'iconid',
        'savedb',
        'sortid',
        'namemode',
        'n_modifyp',
        'n_member_addp',
        'n_member_removep',
    ];
}
