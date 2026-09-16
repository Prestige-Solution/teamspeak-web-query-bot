<?php

namespace Tests\Feature\worker;

use App\Models\User;
use Database\Factories\CreateChannelFactory;
use Database\Factories\CreateChannelGroupFactory;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateServerGroupFactory;
use Database\Factories\CreateWorkerPoliceSettingsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoliceWorkerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    public function test_get_view_police_worker_settings()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('worker.view.upsertPoliceWorker'));
        $response->assertOk();
    }

    public function test_post_update_police_worker_settings()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $updateArray = CreateWorkerPoliceSettingsFactory::new()->make(['discord_webhook_url'=>'https://discord-test.de'])->toArray();

        $response = $this->actingAs($this->user)->post(route('worker.create.updatePoliceWorkerSettings'), $updateArray);
        $response->assertRedirectToRoute('worker.view.upsertPoliceWorker');
    }

    public function test_post_update_police_worker_settings_validation_fails_for_invalid_webhook_url()
    {
        CreateServerFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $updateArray = CreateWorkerPoliceSettingsFactory::new()->make([
            'is_discord_webhook_active' => true,
            'discord_webhook_url' => 'not-a-valid-url',
        ])->toArray();

        $response = $this->actingAs($this->user)->post(route('worker.create.updatePoliceWorkerSettings'), $updateArray);
        $response->assertSessionHasErrors(['discord_webhook_url']);
    }

    public function test_post_update_police_worker_settings_validation_fails_for_missing_email_when_vpn_active()
    {
        CreateServerFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id'=>1]);
        $this->update_user();

        $updateArray = CreateWorkerPoliceSettingsFactory::new()->make([
            'is_vpn_protection_active' => true,
            'vpn_protection_api_register_mail' => null,
        ])->toArray();

        $response = $this->actingAs($this->user)->post(route('worker.create.updatePoliceWorkerSettings'), $updateArray);
        $response->assertSessionHasErrors(['vpn_protection_api_register_mail']);
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', '=', 1)->first();
    }
}
