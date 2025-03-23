<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Models\ts3Bot\ts3BotLog;

class Ts3LogController extends Controller
{
    protected int $server_id;

    protected string $botFunctionName;

    public function __construct($botFunctionName, $server_id)
    {
        $this->server_id = $server_id;
        $this->botFunctionName = $botFunctionName;
    }

    public function setLog($ts3Exception, $botStatus, $job): void
    {
        switch ($ts3Exception->getCode()) {
            case 10061:
                //server not found
                $this->setLogDatabaseEntry(
                    $this->serverID,
                    $botStatus,
                    $job,
                    'Server nicht gefunden oder Offline',
                    $ts3Exception->getCode(),
                    $ts3Exception->getMessage());
                break;
            case 0:
                //connection to server lost
                $this->setLogDatabaseEntry(
                    $this->serverID,
                    $botStatus,
                    $job,
                    'Verbindung zum Server verloren',
                    $ts3Exception->getCode(),
                    $ts3Exception->getMessage());
                break;
            case 513:
                //queryNickname already in use
                $this->setLogDatabaseEntry(
                    $this->serverID,
                    $botStatus,
                    $job,
                    'Query Nickname wird bereits verwendet.',
                    $ts3Exception->getCode(),
                    $ts3Exception->getMessage());
                break;
            case 113:
                //no route to host
                $this->setLogDatabaseEntry(
                    $this->serverID,
                    $botStatus,
                    $job,
                    'Keine Verbindung zum Server möglich',
                    $ts3Exception->getCode(),
                    $ts3Exception->getMessage());
                break;
            case 111:
                //connection refused
                $this->setLogDatabaseEntry(
                    $this->serverID,
                    $botStatus,
                    $job,
                    'Verbindung zum Server wurde abgelehnt',
                    $ts3Exception->getCode(),
                    $ts3Exception->getMessage());
                break;
            default:
                //Unknown Errors
                $this->setLogDatabaseEntry(
                    $this->serverID,
                    $botStatus,
                    $job,
                    'Nicht definierter Fehler',
                    $ts3Exception->getCode(),
                    $ts3Exception->getMessage());
        }

        //debug log
        if (config('app.bot_debug') == true) {
            // print the error message returned by the server
            $errorCodeMsg = 'Server: '.$this->serverID.' | Status: '.$botStatus.' | Bot: '.$this->botFunctionName.' | Job: '.$job.' | Error '.$ts3Exception->getCode().': '.$ts3Exception->getMessage()."\n";
            echo $errorCodeMsg;
        }
    }

    private function setLogDatabaseEntry(int $serverID, int $statusID, string $job, string $desc, $errCode, $errMsg): void
    {
        ts3BotLog::query()->create([
            'server_id'=>$serverID,
            'status_id'=>$statusID,
            'job'=>$job,
            'error_code'=>$errCode,
            'error_message'=>$errMsg,
            'description'=>$desc,
            'worker'=> $this->botFunctionName,
        ]);
    }

    /**
     * @param  null  $errCode
     * @param  null  $errMsg
     */
    public function setCustomLog(int $serverID, int $statusID, string $job, string $desc, $errCode = null, $errMsg = null): void
    {
        ts3BotLog::query()->create([
            'server_id'=>$serverID,
            'status_id'=>$statusID,
            'job'=>$job,
            'error_code'=>$errCode,
            'error_message'=>$errMsg,
            'description'=>$desc,
            'worker'=> $this->botFunctionName,
        ]);

        if (config('app.bot_debug') == true) {
            // print the error message returned by the server
            $errorMsg = 'Server: '.$serverID.' | Status: '.$statusID.' | Job: '.$job.' | Desc: '.$desc.' | Bot: '.$this->botFunctionName."\n";
            echo $errorMsg;
        }
    }
}
