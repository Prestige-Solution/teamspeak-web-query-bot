<?php

namespace Database\Factories;

use App\Models\sys\badName;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreateBadNicknameFactory extends Factory
{
    protected $model = BadName::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id' => 1,
            'description'=>'Factory Test',
            'value_option'=>1,
            'value'=>'Factory',
        ];
    }
}
