<?php

namespace App\Models\category;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catBotJobType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_name',
    ];
}
