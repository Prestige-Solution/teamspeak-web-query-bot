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
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            $validatedIP = $ip;
        }elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            $validatedIP = '['.$ip.']';
        }else
        {
            return 0;
        }

        if ($mode == ts3ServerConfig::TS3ConnectModeRAW)
        {
            return 'serverquery://'.$queryName.':'.Crypt::decryptString($queryPassword).'@'.$validatedIP.':'.$queryPort.
                '/?server_port='.$serverPort.
                '&ssh=0'.
                '&no_query_clients'.
                '&blocking=0'.
                '&timeout=30'.
                '&nickname='.$botName;

        }else
        {
            return 'serverquery://'.$queryName.':'.Crypt::decryptString($queryPassword).'@'.$validatedIP.':'.$queryPort.
                '/?server_port='.$serverPort.
                '&ssh=1'.
                '&no_query_clients'.
                '&blocking=0'.
                '&timeout=30'.
                '&nickname='.$botName;
        }
    }
}
