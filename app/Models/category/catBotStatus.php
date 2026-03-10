<?php

namespace App\Models\category;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catBotStatus extends Model
{
    use HasFactory;

    public static int $running = 1;

    public static int $reconnect = 2;

    public static int $stopped = 3;

    public static int $failed = 4;

    public static int $success = 5;

    protected $fillable = [
        'status_name',
    ];
}
