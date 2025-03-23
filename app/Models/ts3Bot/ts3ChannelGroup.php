<?php

namespace App\Models\ts3Bot;

use Awobaz\Compoships\Compoships;
use Database\Factories\CreateChannelGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3ChannelGroup extends Model
{
    use HasFactory ,Compoships;

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

    public static function newFactory(): CreateChannelGroupFactory
    {
        return CreateChannelGroupFactory::new();
    }
}
