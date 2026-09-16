<?php

namespace Tests\Feature\migration;

use App\Jobs\tsMigrationQueue;
use App\Models\User;
use Database\Factories\CreateServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    public function test_can_view_migration_settings_without_logs(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('migration.view.migrationSettings'));

        $response->assertOk();
        $response->assertViewIs('backend.utils.migration.migrate');
        $response->assertViewHas('servers');
        $response->assertViewHas('logs', '');
    }

    public function test_can_view_migration_settings_with_logs(): void
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $today = now()->format('Y-m-d');
        $logPath = storage_path('logs/migration-'.$today.'.log');
        file_put_contents($logPath, 'Sample migration log content');

        try {
            $response = $this->actingAs($this->user)->get(route('migration.view.migrationSettings'));

            $response->assertOk();
            $response->assertViewIs('backend.utils.migration.migrate');
            $response->assertViewHas('logs', 'Sample migration log content');
            $response->assertSeeText('Sample migration log content');
        } finally {
            if (file_exists($logPath)) {
                unlink($logPath);
            }
        }
    }

    public function test_can_start_migration_successfully(): void
    {
        Queue::fake();

        $sourceServer = CreateServerFactory::new()->create();
        $targetServer = CreateServerFactory::new()->create(['server_ip' => '127.0.0.2']);
        User::query()->where('id', '=', 1)->update(['active_server_id' => $sourceServer->id]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('migration.start.migration'), [
            'source_server_id' => $sourceServer->id,
            'target_server_id' => $targetServer->id,
        ]);

        $response->assertRedirectToRoute('migration.view.migrationSettings');
        $response->assertSessionHas('success', 'Migration started');

        Queue::assertPushedOn('migration', tsMigrationQueue::class, function ($job) use ($sourceServer, $targetServer) {
            return $job->source_server_id === $sourceServer->id && $job->target_server_id === $targetServer->id;
        });
    }

    public function test_start_migration_validation_fails_for_non_existent_server(): void
    {
        Queue::fake();

        $sourceServer = CreateServerFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['active_server_id' => $sourceServer->id]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('migration.start.migration'), [
            'source_server_id' => $sourceServer->id,
            'target_server_id' => 999,
        ]);

        $response->assertSessionHasErrors(['target_server_id']);
        Queue::assertNothingPushed();
    }

    public function test_guest_cannot_access_migration(): void
    {
        $response = $this->get(route('migration.view.migrationSettings'));
        $response->assertRedirectToRoute('public.view.login');

        $response = $this->post(route('migration.start.migration'), [
            'source_server_id' => 1,
            'target_server_id' => 2,
        ]);
        $response->assertRedirectToRoute('public.view.login');
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', '=', 1)->first();
    }
}
