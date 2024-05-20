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
            'storage_path'=>'app/fonts/Arial.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Univers',
            'storage_path'=>'app/fonts/Univers_CE_45_Light.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Univers Bold Italic',
            'storage_path'=>'app/fonts/Univers_LT_66_Bold_Italic.ttf',
        ]);
    }
}
