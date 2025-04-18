<?php

namespace Tests\Feature\server;

use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\User;
use Database\Factories\CreateServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id','=', 1)->first();
    }

    public function test_post_create_new_server()
    {
        $newServer = CreateServerFactory::new()->make()->toArray();

        $response = $this->actingAs($this->user)->post(route('serverConfig.create.server'), $newServer);
        $response->assertRedirectToRoute('serverConfig.view.serverList');

        $checkDB = ts3ServerConfig::query()->get()->first();
        $this->assertEquals($newServer['server_ip'], $checkDB->server_ip);
        $this->assertEquals($newServer['server_name'], $checkDB->server_name);
        $this->assertEquals($newServer['qa_name'], $checkDB->qa_name);
        $this->assertEquals($newServer['qa_pw'], Crypt::decryptString($checkDB->qa_pw));
        $this->assertEquals($newServer['server_query_port'], $checkDB->server_query_port);
        $this->assertEquals($newServer['server_port'], $checkDB->server_port);
    }

    public function test_post_update_server_config()
    {
        CreateServerFactory::new()->create();
        $updateServer = CreateServerFactory::new()->make(['server_name'=>'updated name'])->toArray();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('serverConfig.update.server'), $updateServer);
        $response->assertRedirectToRoute('serverConfig.view.serverList');

        $checkDB = ts3ServerConfig::query()->get()->first();
        $this->assertEquals($updateServer['server_ip'], $checkDB->server_ip);
        $this->assertNotEquals('Factory-Server', $checkDB->server_name);
        $this->assertEquals('updated name', $checkDB->server_name);
        $this->assertEquals($updateServer['qa_name'], $checkDB->qa_name);
        $this->assertEquals($updateServer['qa_pw'], Crypt::decryptString($checkDB->qa_pw));
        $this->assertEquals($updateServer['server_query_port'], $checkDB->server_query_port);
        $this->assertEquals($updateServer['server_port'], $checkDB->server_port);
    }

    public function test_post_switch_active_server()
    {
        CreateServerFactory::new()->create();
        CreateServerFactory::new()->sequence(['user_id' => 1, 'server_ip' => '127.0.0.2'])->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('serverConfig.update.switchDefaultServer'), ['server_id'=>2]);
        $response->assertStatus(302);

        $checkDB = ts3ServerConfig::query()->get();
        $userDB = User::query()->get()->first();

        $this->assertEquals(2, $userDB->default_server_id);
        $this->assertEquals(2, $checkDB->count());
        $this->assertEquals(0, $checkDB->first()->is_default);
        $this->assertEquals(1, $checkDB->last()->is_default);
    }

    public function test_post_delete_server_config()
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('serverConfig.delete.server'), ['server_id'=>1]);
        $response->assertRedirectToRoute('serverConfig.view.serverList');

        $checkDB = ts3ServerConfig::query()->get();
        $userDB = User::query()->where('id', '=', 1)->get()->first();
        $this->assertEquals(0, $checkDB->count());
        $this->assertEquals(0, $userDB->default_server_id);
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id','=', 1)->first();
    }
}
