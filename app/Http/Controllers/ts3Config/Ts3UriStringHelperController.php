<?php

namespace App\Http\Controllers\ts3Config;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;

class Ts3UriStringHelperController extends Controller
{
    /**
     * define standard uri string
     */
    public function getStandardUriString(string $queryName, string $queryPassword, string $ip, string $queryPort, string $serverPort, string $botName, string $ssh = "0"): string
    {
        return 'serverquery://'.$queryName.':'.Crypt::decryptString($queryPassword).'@'.$ip.':'.$queryPort.
            '/?server_port='.$serverPort.
            '&ssh='.$ssh.
            '&no_query_clients'.
            '&blocking=0'.
            '&timeout=30'.
            '&nickname='.$botName;
    }
}
