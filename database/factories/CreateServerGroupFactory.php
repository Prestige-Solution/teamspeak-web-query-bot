<?php

namespace Database\Factories;

use App\Models\ts3Bot\ts3ServerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreateServerGroupFactory extends Factory
{
    protected $model = ts3ServerGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id'=>1,
            'sgid'=>27,
            'name'=>'Community',
            'type'=>1,
            'iconid'=>0,
            'savedb'=>1,
            'sortid'=>103,
            'namemode'=>0,
            'n_modifyp'=>75,
            'n_member_addp'=>70,
            'n_member_removep'=>70,
        ];
    }
}
