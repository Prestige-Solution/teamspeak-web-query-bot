<?php

namespace Database\Factories;

use App\Models\bannerCreator\banner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

class CreateBannerTemplateFactory extends Factory
{
    protected $model = banner::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'banner_name'=>'Factory Banner',
            'banner_original_file_name'=>'template/factory.png',
        ];
    }
}
