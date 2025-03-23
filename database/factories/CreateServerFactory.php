<?php

namespace Database\Factories;

use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class CreateServerFactory extends Factory
{
    protected $model = ts3ServerConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'=>1,
            'server_ip'=>'127.0.0.1',
            'server_name'=>'Factory-Server',
            'qa_name'=>'bot-query-name',
            'qa_pw'=>Crypt::encryptString('password'),
            'server_query_port'=>'10011',
            'server_port'=>'9987',
            'description'=>'description',
            'qa_nickname'=>'bot-nickname',
            'mode'=>1,
        ];
    }
}
