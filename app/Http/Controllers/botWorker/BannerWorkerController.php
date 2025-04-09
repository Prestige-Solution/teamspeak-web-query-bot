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
use Illuminate\Support\Facades\File;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class BannerWorkerController extends Controller
{
    protected int $server_id;

    protected string $qaName;

    protected Ts3LogController $logController;

    protected Server|Adapter|Host|Node $ts3_VirtualServer;

    /**
     * @throws Exception
     */
    public function bannerWorkerCreateBanner(int $server_id): void
    {
        //declare
        $this->server_id = $server_id;
        $this->logController = new Ts3LogController('Banner-Worker', $this->server_id);

        try {
            //proof if banners configured
            $bannerAvailable = banner::query()
                ->where('server_id', '=', $this->server_id)
                ->count();

            if ($bannerAvailable > 0) {
                //get Server config
                $ts3ServerConfig = ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)->first();

                if ($ts3ServerConfig->qa_nickname != null) {
                    $this->qaName = $ts3ServerConfig->qa_nickname;
                } else {
                    $this->qaName = $ts3ServerConfig->qa_name;
                }

                //get the latest unused banner / touch for update updated_at
                $banner = banner::query()
                    ->where('server_id', '=', $this->server_id)
                    ->orderBy('next_check_at')
                    ->first();

                //check if delay arrived
                if (Carbon::now() >= $banner->next_check_at) {
                    //get uri with StringHelper
                    $ts3StringHelper = new Ts3UriStringHelperController();
                    $uri = $ts3StringHelper->getStandardUriString(
                        $ts3ServerConfig->qa_name,
                        $ts3ServerConfig->qa_pw,
                        $ts3ServerConfig->server_ip,
                        $ts3ServerConfig->server_query_port,
                        $ts3ServerConfig->server_port,
                        $this->qaName.'-Banner-Worker',
                        $this->server_id,
                        $ts3ServerConfig->mode
                    );

                    // connect to above specified server, authenticate and spawn an object for the virtual server on port 9987
                    $this->ts3_VirtualServer = TeamSpeak3::factory($uri);
                    $ts3ServerInfo = $this->ts3_VirtualServer->getInfo();

                    //exists banner options
                    $bannerOptions = bannerOption::query()
                        ->with([
                            'rel_cat_banner_option',
                        ])
                        ->where('banner_id', '=', $banner->id)
                        ->get();
                    if ($bannerOptions->count() != 0) {
                        //get create banner dynamic
                        $img = imagecreatefrompng(public_path($banner->banner_original));

                        //get banner options //TODO Fonts
                        foreach ($bannerOptions as $bannerOption) {
                            //font Color
                            list($r, $g, $b) = sscanf($bannerOption->color_hex, '#%02x%02x%02x');
                            $fontColor = imagecolorallocate($img, $r, $g, $b);
                            //get font
                            $fontStyle = storage_path(catFont::query()->where('id', '=',
                                $bannerOption->font_id)->first(['storage_path'])->storage_path);
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
                                        $text = $text.$day.' Tag/e ';
                                    }
                                    if ($hours != 0) {
                                        $text = $text.$hours.' Stunde/n ';
                                    }
                                    if ($minutes != 0) {
                                        $text = $text.$minutes.' Minute/n';
                                    }
                                    break;
                                default:
                                    $text = $bannerOption->text;
                            }

                            //merge options to image
                            imagettftext($img, $fontSize, 0, $coordX, $coordY, $fontColor, $fontStyle, $text);
                        }

                        //renew banner viewer
                        if (File::exists(public_path($banner->banner_viewer)) == true) {
                            //get filename
                            $fileName = $banner->banner_viewer_file_name;
                            //get storage path
                            $file = storage_path('app/public/banner-viewer/server-'.$banner->server_id.'/'.$fileName);
                            $storagePathAsset = 'storage/banner-viewer/server-'.$banner->server_id.'/'.$fileName;
                        } else {
                            //generate new filename
                            $fileName = $banner->server_id.uniqid().'.png';
                            //get storage path
                            $storagePath = storage_path('app/public/banner-viewer/server-'.$banner->server_id);
                            $file = storage_path('app/public/banner-viewer/server-'.$banner->server_id.'/'.$fileName);
                            $storagePathAsset = 'storage/banner-viewer/server-'.$banner->server_id.'/'.$fileName;

                            if (File::exists($storagePath) == false) {
                                File::makeDirectory($storagePath, 0777, true, true);
                            }
                        }

                        //set header type
                        header('Content-Type:image/png');
                        //create banner
                        $successCreated = imagepng($img, $file);
                        imagedestroy($img);

                        //update database
                        if ($successCreated == true) {
                            //update banner
                            banner::query()->where('id', '=', $banner->id)->update([
                                'banner_viewer' => $storagePathAsset,
                                'banner_viewer_file_name' => $fileName,
                                'next_check_at'=>Carbon::now()->addMinutes($banner->delay),
                                'updated_at'=>Carbon::now(),
                            ]);
                        }

                        //set banner location
                        if ($this->ts3_VirtualServer['virtualserver_hostbanner_gfx_url'] != config('app.url').'/'.$storagePathAsset) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_gfx_url'] = config('app.url').'/'.$storagePathAsset;
                        }
                        if ($this->ts3_VirtualServer['virtualserver_hostbanner_url'] != $banner->banner_hostbanner_url) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_url'] = $banner->banner_hostbanner_url;
                        }
                        //update every 5 minutes value is seconds
                        if ($this->ts3_VirtualServer['virtualserver_hostbanner_gfx_interval'] != 180) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_gfx_interval'] = 180;
                        }
                        //update size
                        if ($this->ts3_VirtualServer['virtualserver_hostbanner_mode'] != 2) {
                            $this->ts3_VirtualServer['virtualserver_hostbanner_mode'] = 2;
                        }
                    } else {
                        //set new delay and check to banner
                        banner::query()->where('id', '=', $banner->id)->update([
                            'next_check_at'=>Carbon::now()->addMinutes($banner->delay),
                            'updated_at'=>Carbon::now(),
                        ]);

                        //set banner to virtual server
                        $this->ts3_VirtualServer['virtualserver_hostbanner_gfx_url'] = config('app.url').'/'.$banner->banner_viewer;
                        $this->ts3_VirtualServer['virtualserver_hostbanner_url'] = $banner->banner_hostbanner_url;
                    }
                }

                //disconnect form server
                $this->ts3_VirtualServer->getParent()->getTransport()->disconnect();
            }
        } catch(TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'bannerWorkerCreateBanner');

            //disconnect from server
            $this->ts3_VirtualServer->getParent()->getTransport()->disconnect();
        }
    }
}
