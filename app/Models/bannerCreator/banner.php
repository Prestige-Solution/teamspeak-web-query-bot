<?php

namespace App\Models\bannerCreator;

use Database\Factories\CreateBannerTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'banner_name',
        'banner_original_file_name',
        'banner_viewer_file_name',
        'banner_hostbanner_url',
        'delay',
        'next_check_at',
    ];

    protected static function newFactory(): CreateBannerTemplateFactory
    {
        return CreateBannerTemplateFactory::new();
    }
}
