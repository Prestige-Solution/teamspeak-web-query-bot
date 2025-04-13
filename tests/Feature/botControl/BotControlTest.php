<?php

namespace Tests\Feature\botControl;

use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\User;
use Database\Factories\CreateChannelFactory;
use Database\Factories\CreateChannelGroupFactory;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateServerGroupFactory;
use Database\Factories\CreateWorkerPoliceSettingsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BotControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', 1)->first();
    }

    public function test_post_start_bot()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('ts3.start.ts3Bot'));
        $response->assertStatus(302);
        $response->assertSessionHas(['success' => 'The bot is started and immediately logs onto the server.']);

        //check server config
        $configDB = ts3ServerConfig::query()->get();
        $this->assertTrue((boolean)$configDB->first()->is_ts3_start);
        $this->assertTrue((boolean)$configDB->first()->is_active);

        //check log config
        $logDB = ts3botLog::query()->get();
        $this->assertEquals(1, $logDB->last()->status_id);
        $this->assertEquals('startBot', $logDB->last()->job);
        $this->assertEquals('Bot was started via web interface', $logDB->last()->description);

        //check queue entry
        $queueDB = DB::table('queue_bot')->get();
        $this->assertEquals('bot', $queueDB->last()->queue);
    }

    public function test_post_stop_bot()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('ts3.stop.ts3Bot'));
        $response->assertStatus(302);
        $response->assertSessionHas(['success' => 'Bot is stopped. This may take a moment.']);

        //check server config
        $configDB = ts3ServerConfig::query()->get();
        $this->assertFalse((boolean)$configDB->first()->is_ts3_start);
        $this->assertFalse((boolean)$configDB->first()->is_active);

        //check log config
        $logDB = ts3botLog::query()->get();
        $this->assertEquals(1, $logDB->last()->status_id);
        $this->assertEquals('botStop', $logDB->last()->job);
        $this->assertEquals('Bot was stopped via web interface', $logDB->last()->description);

        //check queue entry
        $queueDB = DB::table('queue_bot')->get();
        $this->assertEquals(0, $queueDB->count());

    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', 1)->first();
    }
}
