<?php

namespace Tests\Feature\commands;

use App\Jobs\tsBannerWorkerQueue;
use App\Jobs\tsBotAfkWorkerQueue;
use App\Jobs\tsBotChannelRemoveWorkerQueue;
use App\Jobs\tsBotPoliceWorkerQueue;
use App\Jobs\tsClearingWorkerQueue;
use App\Models\User;
use Database\Factories\CreateServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ConsoleCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_setup_account_command_creates_or_updates_user(): void
    {
        $this->artisan('app:setup-account')
            ->expectsQuestion('Enter your nickname', 'admin_user')
            ->expectsQuestion('Enter your E-Mail adresse', 'admin@example.com')
            ->expectsQuestion('Enter your password', 'secretPassword123')
            ->expectsQuestion('Confirm your password', 'secretPassword123')
            ->expectsOutput('Your account has been created / updated.')
            ->assertSuccessful();

        $user = User::query()->where('nickname', 'admin_user')->first();
        $this->assertNotNull($user);
        $this->assertEquals('admin@example.com', $user->email);
        $this->assertTrue(Hash::check('secretPassword123', $user->password));
    }

    public function test_setup_account_command_prompts_again_if_passwords_differ(): void
    {
        $this->artisan('app:setup-account')
            ->expectsQuestion('Enter your nickname', 'admin_diff')
            ->expectsQuestion('Enter your E-Mail adresse', 'diff@example.com')
            ->expectsQuestion('Enter your password', 'pass1')
            ->expectsQuestion('Confirm your password', 'pass2')
            ->expectsOutput('Passwords are different.')
            ->expectsQuestion('Enter your password', 'passCorrect')
            ->expectsQuestion('Confirm your password', 'passCorrect')
            ->expectsOutput('Your account has been created / updated.')
            ->assertSuccessful();

        $user = User::query()->where('nickname', 'admin_diff')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('passCorrect', $user->password));
    }

    public function test_dev_clear_conf_command(): void
    {
        $this->artisan('dev:clear-conf')
            ->expectsOutput('View cleared')
            ->expectsOutput('Cache cleared')
            ->expectsOutput('Route cleared')
            ->expectsOutput('Configs cleared')
            ->assertSuccessful();
    }

    public function test_start_worker_command_dispatches_jobs_for_active_servers(): void
    {
        Queue::fake();

        $server1 = CreateServerFactory::new()->create([
            'is_ts_start' => true,
            'is_active' => true,
        ]);
        $server2 = CreateServerFactory::new()->create([
            'server_ip' => '127.0.0.2',
            'is_ts_start' => false,
            'is_active' => true,
        ]);

        $this->artisan('app:start-worker')->assertSuccessful();

        Queue::assertPushedOn('bannerWorker', tsBannerWorkerQueue::class, function ($job) use ($server1) {
            return $job->server_id === $server1->id;
        });
        Queue::assertPushedOn('afkWorker', tsBotAfkWorkerQueue::class, function ($job) use ($server1) {
            return $job->server_id === $server1->id;
        });
        Queue::assertPushedOn('channelRemoverWorker', tsBotChannelRemoveWorkerQueue::class, function ($job) use ($server1) {
            return $job->server_id === $server1->id;
        });
        Queue::assertPushedOn('policeWorker', tsBotPoliceWorkerQueue::class, function ($job) use ($server1) {
            return $job->server_id === $server1->id;
        });

        // Server 2 with is_ts_start = false should not dispatch jobs
        Queue::assertNotPushed(tsBannerWorkerQueue::class, function ($job) use ($server2) {
            return $job->server_id === $server2->id;
        });
    }

    public function test_start_clearing_command_dispatches_jobs_for_active_servers(): void
    {
        Queue::fake();

        $server1 = CreateServerFactory::new()->create([
            'is_ts_start' => true,
            'is_active' => true,
        ]);

        $this->artisan('app:start-clearing')->assertSuccessful();

        Queue::assertPushedOn('clearing', tsClearingWorkerQueue::class, function ($job) use ($server1) {
            return $job->server_id === $server1->id;
        });
    }

    public function test_start_bot_instance_command_finishes_when_no_active_started_server(): void
    {
        // When there are no active started servers, it should complete after checking
        $this->artisan('app:start-bot-instance-command')
            ->assertSuccessful();
    }
}
