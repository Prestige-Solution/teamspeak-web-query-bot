<?php

namespace Database\Seeders;

use App\Models\category\catFont;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class catFontSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        catFont::query()->create([
            'name'=>'Arial',
            'font_name'=>'Arial.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Univers Light',
            'font_name'=>'Univers_CE_45_Light.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Univers Bold Italic',
            'font_name'=>'Univers_LT_66_Bold_Italic.ttf',
        ]);
    }
}
