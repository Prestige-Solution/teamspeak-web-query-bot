<?php

namespace App\Models\category;

use Illuminate\Database\Eloquent\Model;

class catBannerOption extends Model
{
    protected $fillable = [
        'name',
        'pes_code',
        'ts3_attribut',
        'category',
    ];
}
