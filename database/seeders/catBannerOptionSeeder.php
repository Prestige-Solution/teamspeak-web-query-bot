<?php

namespace Database\Seeders;

use App\Models\category\catBannerOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class catBannerOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        catBannerOption::query()->create([
            'name'=>'No options',
            'pes_code'=>'get_no_options',
            'ts3_attribut'=>'no_options',
            'category'=>'no_options',
        ]);

        catBannerOption::query()->create([
            'name'=>'Text',
            'pes_code'=>'get_text',
            'ts3_attribut'=>'text_only',
            'category'=>'text',
        ]);

        catBannerOption::query()->create([
            'name'=>'Max. number of slots',
            'pes_code'=>'get_max_slots',
            'ts3_attribut'=>'virtualserver_maxclients',
            'category'=>'server',
        ]);

        catBannerOption::query()->create([
            'name'=>'Clients online',
            'pes_code'=>'get_clients_online',
            'ts3_attribut'=>'virtualserver_clientsonline',
            'category'=>'server',
        ]);

        catBannerOption::query()->create([
            'name'=>'Plattform',
            'pes_code'=>'get_server_plattform',
            'ts3_attribut'=>'virtualserver_platform',
            'category'=>'server',
        ]);

        catBannerOption::query()->create([
            'name'=>'Latenz',
            'pes_code'=>'get_sever_latency',
            'ts3_attribut'=>'virtualserver_total_ping',
            'category'=>'server',
        ]);

        catBannerOption::query()->create([
            'name'=>'Clients in group online',
            'pes_code'=>'get_server_group_online',
            'ts3_attribut'=>'sgid',
            'category'=>'server_groups',
        ]);

        catBannerOption::query()->create([
            'name'=>'Clients in group',
            'pes_code'=>'get_server_group_max_clients',
            'ts3_attribut'=>'sgid',
            'category'=>'server_groups',
        ]);

        catBannerOption::query()->create([
            'name'=>'Status',
            'pes_code'=>'get_server_status',
            'ts3_attribut'=>'virtualserver_status',
            'category'=>'server',
        ]);

        catBannerOption::query()->create([
            'name'=>'Online since',
            'pes_code'=>'get_online_time',
            'ts3_attribut'=>'virtualserver_uptime',
            'category'=>'server',
        ]);
    }
}
