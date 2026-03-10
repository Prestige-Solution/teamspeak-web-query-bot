<?php

namespace Tests\Feature\server;

use App\Http\Controllers\admin\ResetStatsController;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
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

        $checkDB = ts3BotWorkerPolice::query()->get()->first();
        $this->assertEquals(10, $checkDB->vpn_protection_query_count);

        $resetStatsController = new ResetStatsController();
        $resetStatsController->resetVPNQueryCountPerMinute();

        $checkDB = ts3BotWorkerPolice::query()->get()->first();
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

        $checkDB = ts3BotWorkerPolice::query()->get()->first();
        $this->assertEquals(100, $checkDB->vpn_protection_query_per_day);

        $resetStatsController = new ResetStatsController();
        $resetStatsController->resetVPNQueryPerDay();

        $checkDB = ts3BotWorkerPolice::query()->get()->first();
        $this->assertEquals(0, $checkDB->vpn_protection_query_per_day);
    }

    public function test_delete_bot_logs(): void
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();

        $logController = new Ts3LogController('factory', 1);
        for ($i = 1; $i <= 50; $i++) {
            $logController->setCustomLog(
                1,
                ts3BotLog::SUCCESS,
                'factory test',
                'factory entry '.$i,
            );
        }

        $checkDB = ts3BotLog::query()->get();
        $this->assertCount(50, $checkDB);

        $resetStatsController = new ResetStatsController();
        $resetStatsController->deleteBotLogs();

        $checkDB = ts3BotLog::query()->get();
        $this->assertCount(50, $checkDB);
        $this->assertEquals('factory entry 50', $checkDB->last()->description);
        $this->assertEquals('factory entry 1', $checkDB->first()->description);
        $this->assertEquals('factory entry 50', $checkDB->last()->description);

        for ($i = 51; $i <= 120; $i++) {
            $logController->setCustomLog(
                1,
                ts3BotLog::SUCCESS,
                'factory test',
                'factory entry '.$i,
            );
        }

        $checkDB = ts3BotLog::query()->get();
        $this->assertCount(120, $checkDB);
        $resetStatsController->deleteBotLogs();

        $checkDB = ts3BotLog::query()->get();
        $this->assertCount(100, $checkDB);
        $this->assertEquals('factory entry 21', $checkDB->first()->description);
        $this->assertEquals('factory entry 120', $checkDB->last()->description);
    }
}
