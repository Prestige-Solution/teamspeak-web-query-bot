<?php

namespace App\Http\Controllers\ts3Config;

use App\Http\Controllers\Controller;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Support\Facades\Crypt;

class Ts3UriStringHelperController extends Controller
{
    /**
     * define standard uri string
     * mode = 1 is ssh = 0 equal raw
     * mode = 2 is ssh = 1
     */
    public function getStandardUriString(string $queryName, string $queryPassword, string $host, int|null $queryPort, int $serverPort, string $botName, int $mode = 1): string
    {
        //proof ipv4 or ipv6
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var(gethostbyname($host), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            $validatedHost = $host;
        }elseif (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ||filter_var(gethostbyname($host), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            $validatedHost = '['.$host.']';
        }else
        {
            return false;
        }

        //proof serverPort - if is null then set the standard ports else set the specific given port
        if ($queryPort === null)
        {
            if ($mode == ts3ServerConfig::TS3ConnectModeRAW)
            {
                $queryPort = 10011;
            }else{
                $queryPort = 10022;
            }
        }

        if ($mode == ts3ServerConfig::TS3ConnectModeRAW)
        {
            return 'serverquery://'.$queryName.':'.Crypt::decryptString($queryPassword).'@'.$validatedHost.':'.$queryPort.
                '/?server_port='.$serverPort.
                '&ssh=0'.
                '&no_query_clients=1'.
                '&blocking=0'.
                '&timeout=30'.
                '&nickname='.$botName;

        }else
        {
            return 'serverquery://'.$queryName.':'.Crypt::decryptString($queryPassword).'@'.$validatedHost.':'.$queryPort.
                '/?server_port='.$serverPort.
                '&ssh=1'.
                '&no_query_clients=1'.
                '&blocking=0'.
                '&timeout=30'.
                '&nickname='.$botName;
        }
    }
}
