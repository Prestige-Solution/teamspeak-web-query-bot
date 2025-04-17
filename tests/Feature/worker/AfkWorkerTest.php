<?php

namespace Tests\Feature\worker;

use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use App\Models\User;
use Database\Factories\CreateChannelFactory;
use Database\Factories\CreateChannelGroupFactory;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateServerGroupFactory;
use Database\Factories\UpdateWorkerAfkSettingsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfkWorkerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', 1)->first();
    }

    public function test_get_view_afk_worker_settings()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('worker.view.createOrUpdateAfkWorker'));
        $response->assertOk();
    }

    public function test_post_update_afk_worker_settings()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $updateArray = UpdateWorkerAfkSettingsFactory::new()->make()->toArray();
        $response = $this->actingAs($this->user)->post(route('worker.update.afkWorker'), $updateArray);
        $response->assertRedirectToRoute('worker.view.createOrUpdateAfkWorker');

        $updateArray = UpdateWorkerAfkSettingsFactory::new()->make(['is_afk_active'=>true, 'is_afk_kicker_active'=>true, 'afk_kicker_max_idle_time'=>2])->toArray();
        $response = $this->actingAs($this->user)->post(route('worker.update.afkWorker'), $updateArray);
        $response->assertRedirectToRoute('worker.view.createOrUpdateAfkWorker');

        //checks
        $dbResult = ts3BotWorkerAfk::query()->get();
        $this->assertCount(1, $dbResult);
        $this->assertEquals(1, $dbResult->first()->is_afk_active);
        $this->assertEquals(1, $dbResult->first()->is_afk_kicker_active);
        $this->assertEquals(2 * 1000 * 60, $dbResult->first()->afk_kicker_max_idle_time);
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', 1)->first();
    }
}
