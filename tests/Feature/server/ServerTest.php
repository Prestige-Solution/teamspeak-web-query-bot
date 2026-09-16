<?php

namespace Tests\Feature\server;

use App\Models\tsBot\tsServerConfig;
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

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    public function test_post_create_new_server()
    {
        $newServer = CreateServerFactory::new()->make()->toArray();

        $response = $this->actingAs($this->user)->post(route('serverConfig.create.server'), $newServer);
        $response->assertRedirectToRoute('serverConfig.view.serverList');

        $checkDB = tsServerConfig::query()->get()->first();
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
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('serverConfig.update.server'), $updateServer);
        $response->assertRedirectToRoute('serverConfig.view.serverList');

        $checkDB = tsServerConfig::query()->get()->first();
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
        CreateServerFactory::new()->sequence(['server_ip' => '127.0.0.2'])->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $checkDB = tsServerConfig::query()->get();
        $userDB = User::query()->get()->first();

        $this->assertEquals(1, $userDB->active_server_id);
        $this->assertEquals(2, $checkDB->count());

        $response = $this->actingAs($this->user)->post(route('serverConfig.update.switchDefaultServer'), ['server_id'=>2]);
        $response->assertStatus(302);

        $checkDB = tsServerConfig::query()->get();
        $userDB = User::query()->get()->first();

        $this->assertEquals(2, $userDB->active_server_id);
        $this->assertEquals(2, $checkDB->count());
    }

    public function test_post_delete_server_config()
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('serverConfig.delete.server'), ['server_id'=>1]);
        $response->assertRedirectToRoute('serverConfig.view.serverList');

        $checkDB = tsServerConfig::query()->get();
        $userDB = User::query()->where('id', '=', 1)->get()->first();
        $this->assertEquals(0, $checkDB->count());
        $this->assertEquals(0, $userDB->active_server_id);
    }

    public function test_post_delete_server_switches_active_server_to_remaining_server()
    {
        $server1 = CreateServerFactory::new()->create();
        $server2 = CreateServerFactory::new()->create(['server_ip' => '127.0.0.2']);
        User::query()->where('id', '=', 1)->update(['active_server_id' => $server1->id]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('serverConfig.delete.server'), ['server_id' => $server1->id]);
        $response->assertRedirectToRoute('serverConfig.view.serverList');

        $checkDB = tsServerConfig::query()->get();
        $userDB = User::query()->where('id', '=', 1)->first();
        $this->assertEquals(1, $checkDB->count());
        $this->assertEquals($server2->id, $userDB->active_server_id);
    }

    public function test_post_update_server_initialising_with_valid_server()
    {
        $server = CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id' => $server->id]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('serverConfig.update.serverInit'), ['server_id' => $server->id]);
        $response->assertStatus(302);
    }

    public function test_post_create_server_validation_fails_for_missing_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('serverConfig.create.server'), []);
        $response->assertSessionHasErrors(['server_ip', 'server_name', 'qa_name', 'qa_pw', 'mode']);
    }

    public function test_post_update_server_validation_fails_for_invalid_server_id()
    {
        $response = $this->actingAs($this->user)->post(route('serverConfig.update.server'), [
            'server_id' => 999,
            'server_ip' => '127.0.0.1',
            'server_name' => 'Invalid',
            'qa_name' => 'qa',
            'qa_pw' => 'pw',
            'mode' => 'standard',
        ]);
        $response->assertSessionHasErrors(['server_id']);
    }

    public function test_post_switch_default_server_validation_fails_for_invalid_server()
    {
        $response = $this->actingAs($this->user)->post(route('serverConfig.update.switchDefaultServer'), [
            'server_id' => 999,
        ]);
        $response->assertSessionHasErrors(['server_id']);
    }

    public function test_post_delete_server_validation_fails_for_invalid_server()
    {
        $response = $this->actingAs($this->user)->post(route('serverConfig.delete.server'), [
            'server_id' => 999,
        ]);
        $response->assertSessionHasErrors(['server_id']);
    }

    public function test_post_update_server_init_validation_fails_for_invalid_server()
    {
        $response = $this->actingAs($this->user)->post(route('serverConfig.update.serverInit'), [
            'server_id' => 999,
        ]);
        $response->assertSessionHasErrors(['server_id']);
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', '=', 1)->first();
    }
}
