<?php

namespace Tests\Feature\botControl;

use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsServerConfig;
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

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    public function test_post_start_bot()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('ts.start.tsBot'));
        $response->assertStatus(302);
        $response->assertSessionHas(['success' => 'The bot is started and immediately logs onto the server.']);

        //check server config
        $configDB = tsServerConfig::query()->get();
        $this->assertTrue((bool) $configDB->first()->is_ts_start);
        $this->assertTrue((bool) $configDB->first()->is_active);

        //check log config
        $logDB = tsBotLog::query()->get();
        $this->assertEquals(tsBotLog::SUCCESS, $logDB->last()->status_id);
        $this->assertEquals('startBot', $logDB->last()->job);
        $this->assertEquals('Bot started via web interface', $logDB->last()->description);
    }

    public function test_post_stop_bot()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('ts.stop.tsBot'));
        $response->assertStatus(302);
        $response->assertSessionHas(['success' => 'Bot is shutting down. This may take a moment.']);

        //check server config
        $configDB = tsServerConfig::query()->get();
        $this->assertFalse((bool) $configDB->first()->is_ts_start);
        $this->assertFalse((bool) $configDB->first()->is_active);

        //check log config
        $logDB = tsBotLog::query()->get();
        $this->assertEquals(tsBotLog::SUCCESS, $logDB->last()->status_id);
        $this->assertEquals('botStop', $logDB->last()->job);
        $this->assertEquals('Bot shutting down via web interface', $logDB->last()->description);

        //check queue entry
        $queueDB = DB::table('queue_bot')->get();
        $this->assertEquals(0, $queueDB->count());
    }

    public function test_guest_cannot_start_or_stop_bot(): void
    {
        $response = $this->post(route('ts.start.tsBot'));
        $response->assertRedirectToRoute('public.view.login');

        $response = $this->post(route('ts.stop.tsBot'));
        $response->assertRedirectToRoute('public.view.login');
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', '=', 1)->first();
    }
}
