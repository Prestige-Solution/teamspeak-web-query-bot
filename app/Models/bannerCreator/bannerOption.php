<?php

namespace App\Models\bannerCreator;

use App\Models\category\catBannerOption;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bannerOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'font_id',
        'font_size',
        'color_hex',
        'option_id',
        'extra_option',
        'text',
        'coord_x',
        'coord_y',
    ];

    public function rel_cat_banner_option()
    {
        return $this->hasOne(catBannerOption::class,'id','option_id');
    }
}
