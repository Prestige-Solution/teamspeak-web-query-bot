<?php

namespace App\Models\category;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catBotStatus extends Model
{
    public static int $RUNNING = 1;

    public static int $RECONNECT = 2;

    public static int $SHUTDOWN = 3;

    public static int $FAILED = 4;

    public static int $SUCCESS = 5;

    protected $fillable = [
        'status_name',
    ];
}
