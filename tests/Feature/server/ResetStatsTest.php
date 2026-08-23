<?php

namespace Tests\Feature\server;

use App\Http\Controllers\admin\ResetStatsController;
use App\Http\Controllers\sys\tsLogController;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBotWorkers\tsBotWorkerPolice;
use Database\Factories\CreateChannelFactory;
use Database\Factories\CreateChannelGroupFactory;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateServerGroupFactory;
use Database\Factories\CreateWorkerPoliceSettingsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_reset_vpn_query_count_per_minute()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create([
            'vpn_protection_query_count' => 10,
        ]);

        $checkDB = tsBotWorkerPolice::query()->get()->first();
        $this->assertEquals(10, $checkDB->vpn_protection_query_count);

        $resetStatsController = new ResetStatsController();
        $resetStatsController->resetVPNQueryCountPerMinute();

        $checkDB = tsBotWorkerPolice::query()->get()->first();
        $this->assertEquals(0, $checkDB->vpn_protection_query_count);
    }

    public function test_reset_vpn_query_count_per_day()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create([
            'vpn_protection_query_per_day' => 100,
        ]);

        $checkDB = tsBotWorkerPolice::query()->get()->first();
        $this->assertEquals(100, $checkDB->vpn_protection_query_per_day);

        $resetStatsController = new ResetStatsController();
        $resetStatsController->resetVPNQueryPerDay();

        $checkDB = tsBotWorkerPolice::query()->get()->first();
        $this->assertEquals(0, $checkDB->vpn_protection_query_per_day);
    }

    public function test_delete_bot_logs(): void
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();

        $logController = new tsLogController('factory', 1);
        for ($i = 1; $i <= 50; $i++) {
            $logController->setCustomLog(
                1,
                tsBotLog::SUCCESS,
                'factory test',
                'factory entry '.$i,
            );
        }

        $checkDB = tsBotLog::query()->get();
        $this->assertCount(50, $checkDB);

        $resetStatsController = new ResetStatsController();
        $resetStatsController->deleteBotLogs();

        $checkDB = tsBotLog::query()->get();
        $this->assertCount(50, $checkDB);
        $this->assertEquals('factory entry 50', $checkDB->last()->description);
        $this->assertEquals('factory entry 1', $checkDB->first()->description);
        $this->assertEquals('factory entry 50', $checkDB->last()->description);

        for ($i = 51; $i <= 120; $i++) {
            $logController->setCustomLog(
                1,
                tsBotLog::SUCCESS,
                'factory test',
                'factory entry '.$i,
            );
        }

        $checkDB = tsBotLog::query()->get();
        $this->assertCount(120, $checkDB);
        $resetStatsController->deleteBotLogs();

        $checkDB = tsBotLog::query()->get();
        $this->assertCount(100, $checkDB);
        $this->assertEquals('factory entry 21', $checkDB->first()->description);
        $this->assertEquals('factory entry 120', $checkDB->last()->description);
    }
}
