<?php

namespace Database\Factories;

use App\Models\bannerCreator\bannerOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreateBannerViewerFactory extends Factory
{
    protected $model = bannerOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => 1,
            'coord_x'=>400,
            'coord_y'=>400,
            'font_id'=>1,
            'font_size'=>56,
            'color_hex'=>'#FF4040',
            'delay'=>1,
            'option_id'=>2,
            'extra_option'=>0,
            'text'=>'Factory Test',
            'banner_hostbanner_url'=>'https://factory-domain.de',
        ];
    }
}
