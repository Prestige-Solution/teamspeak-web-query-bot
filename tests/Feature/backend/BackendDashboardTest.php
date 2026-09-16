<?php

namespace Tests\Feature\backend;

use App\Http\Controllers\sys\TsLogController;
use App\Models\tsBot\tsBotLog;
use App\Models\User;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateStatisticFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    public function test_can_view_dashboard_with_active_server_and_statistics(): void
    {
        $server = CreateServerFactory::new()->create();
        CreateStatisticFactory::new()->create(['server_id' => $server->id]);
        User::query()->where('id', '=', 1)->update(['active_server_id' => $server->id]);
        $this->update_user();

        $logController = new TsLogController('DashboardTest', $server->id);
        $logController->setCustomLog($server->id, tsBotLog::SUCCESS, 'testJob', 'Sample log message');

        $response = $this->actingAs($this->user)->get(route('backend.view.dashboard'));

        $response->assertOk();
        $response->assertViewIs('backend.dashboard.dashboard');
        $response->assertViewHas('stats');
        $response->assertViewHas('server');
        $response->assertViewHas('availableServers');
        $response->assertViewHas('botLogs');
    }

    public function test_can_view_dashboard_without_active_server(): void
    {
        User::query()->where('id', '=', 1)->update(['active_server_id' => 0]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('backend.view.dashboard'));

        $response->assertOk();
        $response->assertViewIs('backend.dashboard.dashboard');
    }

    public function test_can_view_control_center_with_active_server(): void
    {
        $server = CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id' => $server->id]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('backend.view.botControlCenter'));

        $response->assertOk();
        $response->assertViewIs('backend.control-center.bot-control');
        $response->assertViewHas('server');
        $response->assertViewHas('availableServers');
    }

    public function test_can_view_control_center_without_active_server(): void
    {
        User::query()->where('id', '=', 1)->update(['active_server_id' => 0]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('backend.view.botControlCenter'));

        $response->assertOk();
        $response->assertSeeText('No server has been found. you can manage your servers here');
    }

    public function test_can_view_bot_logs_with_active_server_and_logs(): void
    {
        $server = CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id' => $server->id]);
        $this->update_user();

        $logController = new TsLogController('LogTest', $server->id);
        $logController->setCustomLog($server->id, tsBotLog::SUCCESS, 'botJob', 'Specific log entry for testing');

        $response = $this->actingAs($this->user)->get(route('backend.view.botLogs'));

        $response->assertOk();
        $response->assertViewIs('backend.control-center.bot-logs');
        $response->assertViewHas('botLogs');
        $response->assertSeeText('Specific log entry for testing');
    }

    public function test_can_view_password_reset(): void
    {
        $response = $this->actingAs($this->user)->get(route('backend.view.changePassword'));

        $response->assertOk();
        $response->assertViewIs('auth.changePassword');
    }

    public function test_can_update_password_successfully(): void
    {
        $response = $this->actingAs($this->user)->post(route('backend.update.changePassword'), [
            'CurrentPassword' => 'test',
            'NewPassword' => 'newSecretPass123',
            'NewPassword_confirmation' => 'newSecretPass123',
        ]);

        $response->assertRedirectToRoute('backend.view.dashboard');
        $response->assertSessionHas('success', 'Password changed successfully.');

        // Test login with new password
        $loginResponse = $this->post(route('logging-in'), [
            'nickname' => $this->user->nickname,
            'password' => 'newSecretPass123',
        ]);
        $loginResponse->assertRedirectToRoute('backend.view.dashboard');
    }

    public function test_update_password_fails_with_invalid_current_password(): void
    {
        $response = $this->actingAs($this->user)->post(route('backend.update.changePassword'), [
            'CurrentPassword' => 'wrongPassword',
            'NewPassword' => 'newSecretPass123',
            'NewPassword_confirmation' => 'newSecretPass123',
        ]);

        $response->assertSessionHasErrors(['CurrentPassword']);
    }

    public function test_update_password_fails_with_unmatched_confirmation(): void
    {
        $response = $this->actingAs($this->user)->post(route('backend.update.changePassword'), [
            'CurrentPassword' => 'test',
            'NewPassword' => 'newSecretPass123',
            'NewPassword_confirmation' => 'mismatchPass',
        ]);

        $response->assertSessionHasErrors(['NewPassword']);
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', '=', 1)->first();
    }
}
