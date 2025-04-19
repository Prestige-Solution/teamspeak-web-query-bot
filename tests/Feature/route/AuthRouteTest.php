<?php

namespace Tests\Feature\route;

use App\Models\User;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateWorkerPoliceSettingsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    public function test_can_view_control_center(): void
    {
        $response = $this->actingAs($this->user)->get(route('backend.view.botControlCenter'));

        $response->assertStatus(200);
        $response->assertSeeText('No server has been found. you can manage your servers here');
        $response->assertViewIs('backend.control-center.bot-control');
    }

    public function test_can_view_password_reset(): void
    {
        $response = $this->actingAs($this->user)->get(route('backend.view.changePassword'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.changePassword');
    }

    public function test_can_view_bot_logs(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('backend.view.botLogs'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.control-center.bot-logs');
    }

    public function test_can_view_server_list(): void
    {
        $response = $this->actingAs($this->user)->get(route('serverConfig.view.serverList'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.server.servers');
    }

    public function test_can_view_channels_channel_creator_job_list(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('channel.view.channelJobs'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.jobs.channel-creator.channel-creator-job-list');
    }

    public function test_can_view_channels_channel_remover_job_list(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('channel.view.listChannelRemover'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.jobs.channel-remover.channel-remover-job-list');
    }

    public function test_can_view_worker_afk_settings(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('worker.view.createOrUpdateAfkWorker'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.jobs.worker.afk.worker-settings-afk');
    }

    public function test_can_view_worker_police_settings(): void
    {
        CreateServerFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('worker.view.upsertPoliceWorker'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.jobs.worker.police.worker-settings-police');
    }

    public function test_can_view_worker_bad_nicknames_list(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('worker.view.badNames'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.jobs.worker.bad-names.worker-bad-names');
    }

    public function test_can_view_banner_list(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('banner.view.listBanner'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.banner-creator.banner-list');
    }

    public function update_user(): void
    {
        $this->user = User::query()->where('id', '=', 1)->first();
    }
}
