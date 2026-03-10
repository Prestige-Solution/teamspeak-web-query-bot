<?php

namespace App\Models\bannerCreator;

use App\Models\category\catBannerOption;
use Database\Factories\CreateBannerViewerFactory;
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

    protected static function newFactory(): CreateBannerViewerFactory
    {
        return CreateBannerViewerFactory::new();
    }

    public function rel_cat_banner_option(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(catBannerOption::class, 'id', 'option_id');
    }
}
