<?php

namespace App\Http\Controllers\ts3Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use Exception;
use Illuminate\Support\Facades\Crypt;

class Ts3UriStringHelperController extends Controller
{
    /**
     * define standard uri string
     * mode = 1 is ssh = 0 equal raw
     * mode = 2 is ssh = 1
     * @throws Exception
     */
    public function getStandardUriString(string $queryName, string $queryPassword, string $host, int|null $queryPort, int $serverPort, string $botName, int $server_id): string
    {
        //proof ipv4 or ipv6
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var(gethostbyname($host), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $validatedHost = $host;
        } elseif (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || filter_var(gethostbyname($host), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $validatedHost = '['.$host.']';
        } else {
            $logController = new Ts3LogController('validate uri', $server_id);
            $logController->setCustomLog(
                $server_id,
                ts3BotLog::FAILED,
                'validate uri',
                'invalid server address',
            );

            throw new Exception('Invalid Server IP');
        }

        //proof serverPort - if is null then set the standard ports else set the specific given port
        if ($queryPort === null) {
            $queryPort = 10022;
        }

        return 'serverquery://'.$queryName.':'.Crypt::decryptString($queryPassword).'@'.$validatedHost.':'.$queryPort.
            '/?server_port='.$serverPort.
            '&no_query_clients=0'.
            '&nickname='.$botName;
    }
}
