<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\bannerCreator\banner;
use App\Models\bannerCreator\bannerOption;
use App\Models\category\catFont;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class BannerWorkerController extends Controller
{
    protected int $server_id;

    protected string $qa_name;

    protected Ts3LogController $logController;

    protected Server|Adapter|Host|Node $ts3_VirtualServer;

    public function __construct(int $server_id)
    {
        $this->server_id = $server_id;
        $this->logController = new Ts3LogController('Banner-Worker', $this->server_id);
    }

    /**
     * @throws Exception
     */
    public function bannerWorkerCreateBanner(): void
    {
        try {
            $bannerAvailable = banner::query()
                ->where('server_id', '=', $this->server_id)
                ->get()
                ->count();

            if ($bannerAvailable > 0) {
                //get Server config
                $ts3ServerConfig = ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)->first();

                if ($ts3ServerConfig->qa_nickname != null) {
                    $this->qa_name = $ts3ServerConfig->qa_nickname;
                } else {
                    $this->qa_name = $ts3ServerConfig->qa_name;
                }

                //get the latest unused banner
                $banner = banner::query()
                    ->where('server_id', '=', $this->server_id)
                    ->orderBy('next_check_at')
                    ->first();

                if (Storage::disk('banner')->exists('template/'.$banner->banner_original_file_name) == false) {
                    $this->logController->setCustomLog(
                        $this->server_id,
                        ts3BotLog::FAILED,
                        'bannerWorkerCreateBanner',
                        'Original banner template file could not be found.',
                    );

                    return;
                }

                //get uri with StringHelper
                $ts3StringHelper = new Ts3UriStringHelperController();
                $uri = $ts3StringHelper->getStandardUriString(
                    $ts3ServerConfig->qa_name,
                    $ts3ServerConfig->qa_pw,
                    $ts3ServerConfig->server_ip,
                    $ts3ServerConfig->server_query_port,
                    $ts3ServerConfig->server_port,
                    $this->qa_name.'-Banner-Worker',
                    $this->server_id,
                    $ts3ServerConfig->mode
                );

                $this->ts3_VirtualServer = TeamSpeak3::factory($uri);
                $ts3ServerInfo = $this->ts3_VirtualServer->getInfo();

                //check if delay arrived
                if (Carbon::now() >= $banner->next_check_at) {
                    //exists banner options
                    $bannerOptions = bannerOption::query()
                        ->with([
                            'rel_cat_banner_option',
                        ])
                        ->where('banner_id', '=', $banner->id)
                        ->get();

                    if ($bannerOptions->count() != 0) {
                        //get create banner dynamic
                        $img = imagecreatefrompng(Storage::disk('banner')->path('template/'.$banner->banner_original_file_name));

                        //get banner options
                        foreach ($bannerOptions as $bannerOption) {
                            //font Color
                            list($r, $g, $b) = sscanf($bannerOption->color_hex, '#%02x%02x%02x');
                            $fontColor = imagecolorallocate($img, $r, $g, $b);
                            //get font
                            $fontStyle = Storage::disk('fonts')->path(catFont::query()->where('id', '=', $bannerOption->font_id)->first()->font_name);
                            //get fontsize
                            $fontSize = $bannerOption->font_size;
                            //get coordinates
                            $coordX = $bannerOption->coord_x;
                            $coordY = $bannerOption->coord_y;

                            //get dynamic options
                            switch ($bannerOption->rel_cat_banner_option->pes_code) {
                                case 'get_max_slots':
                                    $text = $ts3ServerInfo['virtualserver_maxclients'];
                                    break;
                                case 'get_clients_online':
                                    $text = $ts3ServerInfo['virtualserver_clientsonline'];
                                    break;
                                case 'get_server_plattform':
                                    $text = $ts3ServerInfo['virtualserver_platform'];
                                    break;
                                case 'get_sever_latency':
                                    $text = $ts3ServerInfo['virtualserver_total_ping'];
                                    $pos = strpos($text, '.');
                                    $text = substr($text, 0, $pos);
                                    break;
                                case 'get_server_group_online':
                                    $clientGroupOnline = collect($this->ts3_VirtualServer->clientList(['client_servergroups' => $bannerOption->extra_option]));
                                    $text = $clientGroupOnline->count();
                                    break;
                                case 'get_server_group_max_clients':
                                    $clientGroupCount = collect($this->ts3_VirtualServer->serverGroupClientList($bannerOption->extra_option));
                                    $text = $clientGroupCount->count();
                                    break;
                                case 'get_server_status':
                                    $text = $ts3ServerInfo['virtualserver_status'];
                                    break;
                                case 'get_online_time':
                                    $timeSeconds = $ts3ServerInfo['virtualserver_uptime'];
                                    $day = floor($timeSeconds / 86400);
                                    $hours = floor(($timeSeconds - ($day * 86400)) / 3600);
                                    $minutes = floor(($timeSeconds / 60) % 60);
                                    $text = '';
                                    if ($day != 0) {
                                        $text = $text.$day.' Day/s ';
                                    }
                                    if ($hours != 0) {
                                        $text = $text.$hours.' Hour/s ';
                                    }
                                    if ($minutes != 0) {
                                        $text = $text.$minutes.' Minute/s';
                                    }
                                    break;
                                default:
                                    $text = $bannerOption->text;
                            }

                            //merge options with image
                            imagettftext($img, $fontSize, 0, $coordX, $coordY, $fontColor, $fontStyle, $text);
                        }

                        //renew banner viewer
                        if (Storage::disk('banner')->exists('viewer/'.$banner->banner_viewer_file_name) === true) {
                            $fileName = $banner->banner_viewer_file_name;
                        } else {
                            $this->logController->setCustomLog(
                                $this->server_id,
                                ts3BotLog::FAILED,
                                'bannerWorkerCreateBanner',
                                'Viewer file name could not be found.');

                            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();

                            return;
                        }

                        $filePath = Storage::disk('banner')->path('viewer/'.$fileName);

                        //set header type
                        header('Content-Type:image/png');
                        //create banner
                        $successCreated = imagepng($img, $filePath);
                        imagedestroy($img);

                        //update database
                        if ($successCreated == true) {
                            banner::query()->where('id', '=', $banner->id)->update([
                                'next_check_at'=>Carbon::now()->addMinutes($banner->delay),
                            ]);
                        }

                        if ($ts3ServerInfo['virtualserver_hostbanner_gfx_url'] != asset('banner/viewer/'.$banner->banner_viewer_file_name)) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_gfx_url'] = asset('banner/viewer/'.$banner->banner_viewer_file_name);
                        }
                        if ($ts3ServerInfo['virtualserver_hostbanner_url'] != $banner->banner_hostbanner_url) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_url'] = $banner->banner_hostbanner_url;
                        }
                        //update every x minutes - ts3 server side
                        if ($ts3ServerInfo['virtualserver_hostbanner_gfx_interval'] != 180) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_gfx_interval'] = 180;
                        }
                        //update size
                        if ($ts3ServerInfo['virtualserver_hostbanner_mode'] != 2) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_mode'] = 2;
                        }
                    } else {
                        banner::query()->where('id', '=', $banner->id)->update([
                            'next_check_at'=>Carbon::now()->addMinutes($banner->delay),
                        ]);

                        $this->ts3_VirtualServer['virtualserver_hostbanner_gfx_url'] = '';
                        $this->ts3_VirtualServer['virtualserver_hostbanner_url'] = '';
                    }
                    //update updated_at
                    $banner->touch();
                }

                $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
            }
        } catch(Exception $e) {
            $this->logController->setCustomLog($this->server_id,
                ts3BotLog::FAILED,
                'bannerWorkerCreateBanner',
                'There was an error during create banner',
                $e->getCode(),
                $e->getMessage()
            );

            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
