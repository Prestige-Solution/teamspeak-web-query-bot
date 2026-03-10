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
    public function run(): void
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

        catFont::query()->create([
            'name'=>'Baroque Script',
            'font_name'=>'BaroqueScript.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Broadcast Titling',
            'font_name'=>'BroadcastTitling.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Canterbury',
            'font_name'=>'Canterbury.ttf',
        ]);

        catFont::query()->create([
            'name'=>'OldEnglish Regular',
            'font_name'=>'OldEnglishRegular.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Rothenburg Decorative',
            'font_name'=>'RothenburgDecorative.ttf',
        ]);

        catFont::query()->create([
            'name'=>'Transformers Movie',
            'font_name'=>'TransformersMovie.ttf',
        ]);
    }
}
