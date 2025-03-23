<?php

namespace App\Models\ts3Bot;

use Awobaz\Compoships\Compoships;
use Database\Factories\CreateServerGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3ServerGroup extends Model
{
    use HasFactory, Compoships;

    protected $fillable = [
        'server_id',
        'sgid',
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

    protected static function newFactory(): CreateServerGroupFactory
    {
        return CreateServerGroupFactory::new();
    }
}
