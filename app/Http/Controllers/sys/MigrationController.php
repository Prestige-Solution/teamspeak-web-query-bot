<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Http\Requests\Migration\StartMigrationRequest;
use App\Jobs\tsMigrationQueue;
use App\Models\tsBot\tsServerConfig;
use Illuminate\Support\Facades\Auth;

class MigrationController extends Controller
{
    public function viewMigration()
    {
        $servers = tsServerConfig::query()
            ->where('id', '=', Auth::user()->active_server_id)
            ->get();

        //get logs
        $today = now()->format('Y-m-d');
        if (file_exists(storage_path('logs/migration-'.$today.'.log'))) {
            $logPath = storage_path('logs/migration-'.$today.'.log');
            $log = file_get_contents($logPath);
        } else {
            $log = '';
        }

        return view('backend.utils.migration.migrate')->with([
            'servers'=>$servers,
            'logs'=>$log,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function startMigration(StartMigrationRequest $request)
    {
        $this->resetLogs();

        tsMigrationQueue::dispatch($request->validated('source_server_id'), $request->validated('target_server_id'))->onConnection('worker')->onQueue('migration');

        return redirect()->route('migration.view.migrationSettings')->with('success', 'Migration started');
    }

    private function resetLogs(): void
    {
        $logFiles = glob(storage_path('logs/migration*.log'));
        if ($logFiles !== false) {
            foreach ($logFiles as $logFile) {
                if (file_exists($logFile)) {
                    unlink($logFile);
                }
            }
        }
    }
}
