<?php

namespace App\Models\sys;

use Database\Factories\CreateBadNicknameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class badName extends Model
{
    use HasFactory;

    public const stringContains = 1;

    public const stringRegex = 2;

    protected $fillable = [
        'server_id',
        'description',
        'value_option',
        'value',
        'is_failed',
    ];

    protected static function newFactory(): CreateBadNicknameFactory
    {
        return CreateBadNicknameFactory::new();
    }
}
