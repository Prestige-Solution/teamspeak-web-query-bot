<?php

namespace App\Models\category;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catBotStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_name',
    ];
}
