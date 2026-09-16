<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Models\tsBot\tsBotLog;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;

class TsLogController extends Controller
{
    protected int $server_id;

    protected string $botFunctionName;

    public function __construct(string $botFunctionName, int $server_id)
    {
        $this->server_id = $server_id;
        $this->botFunctionName = $botFunctionName;
    }

    /**
     *  Set log entrys from known error codes
     */
    public function setLog(TeamSpeak3Exception $tsException, int $botStatus, string $job): void
    {
        switch ($tsException->getCode()) {
            case 10061:
                //server not found
                $this->setLogDatabaseEntry(
                    $this->server_id,
                    $botStatus,
                    $job,
                    'The server was not found or is offline',
                    $tsException->getCode(),
                    $tsException->getMessage());
                break;
            case 0:
                //connection to server lost
                $this->setLogDatabaseEntry(
                    $this->server_id,
                    $botStatus,
                    $job,
                    'Connection to server lost',
                    $tsException->getCode(),
                    $tsException->getMessage());
                break;
            case 513:
                //queryNickname already in use
                $this->setLogDatabaseEntry(
                    $this->server_id,
                    $botStatus,
                    $job,
                    'Query nickname is already in use',
                    $tsException->getCode(),
                    $tsException->getMessage());
                break;
            case 113:
                //no route to host
                $this->setLogDatabaseEntry(
                    $this->server_id,
                    $botStatus,
                    $job,
                    'Connection to server is not possible',
                    $tsException->getCode(),
                    $tsException->getMessage());
                break;
            case 111:
                //connection refused
                $this->setLogDatabaseEntry(
                    $this->server_id,
                    $botStatus,
                    $job,
                    'Connection to server was rejected',
                    $tsException->getCode(),
                    $tsException->getMessage());
                break;
            default:
                //Unknown Errors
                $this->setLogDatabaseEntry(
                    $this->server_id,
                    $botStatus,
                    $job,
                    'Undefined error',
                    $tsException->getCode(),
                    $tsException->getMessage());
        }
    }

    /**
     * Set custom log entrys
     * @param  null  $errCode
     * @param  null  $errMsg
     */
    public function setCustomLog(int $server_id, int $status_id, string $job, string $description, $errCode = null, $errMsg = null): void
    {
        $this->setLogDatabaseEntry($server_id, $status_id, $job, $description, $errCode, $errMsg);
    }

    public function deleteLogEntrysByServerID(): void
    {
        tsBotLog::query()->where('server_id', '=', $this->server_id)->delete();
    }

    private function setLogDatabaseEntry(int $server_id, int $status_id, string $job, string $description, $errCode, $errMsg): void
    {
        tsBotLog::query()->create([
            'server_id'=>$server_id,
            'status_id'=>$status_id,
            'job'=>$job,
            'error_code'=>$errCode,
            'error_message'=>$errMsg,
            'description'=>$description,
            'worker'=> $this->botFunctionName,
        ]);

        if (config('app.bot_debug') == true) {
            // print the error message returned by the server
            $errorMsg = 'Server: '.$server_id.' | Status: '.$status_id.' | Job: '.$job.' | Desc: '.$description.' | Bot: '.$this->botFunctionName.' | MSG: '.$errMsg.' | ErrCode: '.$errCode."\n";
            echo $errorMsg;
        }
    }
}
