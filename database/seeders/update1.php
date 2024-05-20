<?php

namespace Database\Seeders;

use App\Models\category\catBannerOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class update1 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        catBannerOption::query()->create([
            'name'=>'Server Status',
            'pes_code'=>'get_server_status',
            'ts3_attribut'=>'virtualserver_status',
            'category'=>'server',
        ]);

        catBannerOption::query()->create([
            'name'=>'Server online seit',
            'pes_code'=>'get_online_time',
            'ts3_attribut'=>'virtualserver_uptime',
            'category'=>'server',
        ]);
    }
}
