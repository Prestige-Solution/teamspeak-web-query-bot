<?php

namespace App\Http\Controllers\banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\CreateUploadedBannerRequest;
use App\Http\Requests\Banner\ViewCreateBannerRequest;
use App\Http\Requests\Banner\ViewListBannerRequest;
use App\Models\bannerCreator\banner;
use App\Models\bannerCreator\bannerOption;
use App\Models\category\catBannerOption;
use App\Models\category\catFont;
use App\Models\ts3Bot\ts3ServerGroup;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next)
        {
            if(Auth::user()->server_id == $request->input('server_id') || Auth::user()->server_id == $request->input('ServerID'))
            {
                return $next($request);
            }
            else
            {
                if($request->has('bannerID'))
                {
                    $serverID = banner::query()->where('id','=',$request->input('bannerID'))->first();

                    if ($serverID != null && Auth::user()->server_id == $serverID->server_id)
                    {
                        return $next($request);
                    }
                }
                if($request->has('DeleteBannerID'))
                {
                    $serverID = banner::query()->where('id','=',$request->input('DeleteBannerID'))->first();

                    if ($serverID != null && Auth::user()->server_id == $serverID->server_id)
                    {
                        return $next($request);
                    }
                }

                return redirect()->route('start.view.dashboard');
            }
        });
    }

    public function viewListBanner(ViewListBannerRequest $request): View|Factory|RedirectResponse|Application
    {
        $banners = banner::query()->where('server_id','=',$request->validated('server_id'))->get();

        return view('backend.banner-creator.list-banner')->with([
            'serverID'=>$request->validated('server_id'),
            'banners'=>$banners,
        ]);
    }

    public function viewCreateBanner(ViewCreateBannerRequest $request): View|Factory|RedirectResponse|Application
    {
        $banner = banner::query()->where('id','=',$request->validated('bannerID'))->first();
        $bannerOptions = catBannerOption::query()->get();
        $bannerFonts = catFont::query()->get();
        $serverID = banner::query()->where('id','=',$request->validated('bannerID'))->first(['server_id']);
        $serverGroups = ts3ServerGroup::query()
            ->where('server_id','=',$serverID->server_id)
            ->where('type','=',1)
            ->get();
        $storedBannerOptions = bannerOption::query()
            ->where('banner_id','=',$request->validated('bannerID'))->get();

        if ($storedBannerOptions->count() != 0)
        {
            $optionsAvailable = true;
        }else
        {
            $optionsAvailable = false;
        }

        return view('backend.banner-creator.create-banner')->with([
            'banner'=>$banner,
            'bannerFonts'=>$bannerFonts,
            'bannerOptions'=>$bannerOptions,
            'serverGroups'=>$serverGroups,
            'storedBannerOptions'=>$storedBannerOptions,
            'optionsStored'=>$optionsAvailable,
        ]);
    }

    public function createUploadedBanner(CreateUploadedBannerRequest $request): RedirectResponse
    {
        //File Upload
        $fileType = $request->file('BannerUploadFile')->extension();
        $fileNameOriginal = $request->validated('ServerID').uniqid().'.'.$fileType;
        //Store (pfad, driver)
        $request->file('BannerUploadFile')->storeAs('banner-template/server-'.$request->validated('ServerID').'/', $fileNameOriginal, 'public');
        //create path for db entry
        $fileStoragePathOriginal = 'storage/banner-template/server-'.$request->validated('ServerID').'/'.$fileNameOriginal;

        banner::query()->create([
            'server_id'=>$request->validated('ServerID'),
            'banner_original'=>$fileStoragePathOriginal,
            'banner_original_file_name'=>$fileNameOriginal,
            'delay'=>1,
            'next_check_at'=>Carbon::now(),
        ]);

        return redirect()->route('banner.view.listBanner',['server_id'=>$request->validated('ServerID')]);
    }

    public function updateBanner(Request $request): RedirectResponse
    {
        //TODO validation and extract delete banner in separate function

        if ($request->has('bannerID'))
        {
            //return code 0 == success
            $successCode = $this->createBanner($request);

            return redirect()->route('banner.view.createBanner',['bannerID'=>$request->input('bannerID')]);
        }

        if ($request->has('DeleteBannerID'))
        {
            $serverID = $this->deleteBanner($request->input('DeleteBannerID'));

            return redirect()->route('banner.view.listBanner',['server_id'=>$serverID]);
        }

        return redirect()->route('backend.view.server');
    }

    private function createBanner(Request $request): int
    {
        $banner = banner::query()->where('id','=',$request->input('bannerID'))->first();

        //create non-dynamic input
        $img = imagecreatefrompng(public_path($banner->banner_original));
        //change color form hex to rgb
        $fontColorList = list($r,$g,$b) = sscanf($request->input('ColorSelect'), "#%02x%02x%02x");
        $fontColor = imagecolorallocate($img,$r,$g,$b);
        //get font
        $fontStyle = catFont::query()->where('id','=',$request->input('FontOption'))->first(['storage_path']);
        $fontStyle = storage_path($fontStyle->storage_path);

        //get options
        $countBannerOption1 = collect($request->input('BannerOption1'))->count();
        $bannerOption1 = collect($request->input('BannerOption1'));
        $bannerOption2 = collect($request->input('BannerOption2'));
        $coordXs = collect($request->input('CoordX'));
        $coordYs = collect($request->input('CoordY'));
        $texts = collect($request->input('Text'));
        $availableBannerOptions = catBannerOption::query()->get(['id','pes_code']);

        //delete all current options
        bannerOption::query()
            ->where('banner_id','=',$request->input('bannerID'))
            ->delete();

        for ($i=0; $i < $countBannerOption1; $i++)
        {
            if ($bannerOption1[$i] != 'delete' && $availableBannerOptions->where('id','=',$bannerOption1[$i])->first()->pes_code != 'get_no_options')
            {
                //define default
                $text = "0";

                //if set text options
                if ($texts[$i] != NUll && $availableBannerOptions->where('id','=',$bannerOption1[$i])->first()->pes_code == 'get_text')
                {
                    $text = $texts[$i];
                }

                imagettftext($img,$request->input('FontSize'),0, $coordXs[$i],$coordYs[$i],$fontColor,$fontStyle,$text);

                //store option
                bannerOption::query()->create([
                    'banner_id'=>$request->input('bannerID'),
                    'font_id'=>$request->input('FontOption'),
                    'font_size'=>$request->input('FontSize'),
                    'color_hex'=>$request->input('ColorSelect'),
                    'option_id'=>$bannerOption1[$i],
                    'extra_option'=>$bannerOption2[$i],
                    'text'=>$text,
                    'coord_x'=>$coordXs[$i],
                    'coord_y'=>$coordYs[$i],
                ]);
            }
        }

        //set new filename
        $fileName = $banner->server_id . uniqid().'.png';

        //proof if file exist
        if($banner->banner_viewer != NULL && File::exists(public_path($banner->banner_viewer)) == true)
        {
            //get storage path
            $file = storage_path('app/public/banner-viewer/server-'.$banner->server_id."/".$fileName);
            $storagePathAsset = 'storage/banner-viewer/server-'.$banner->server_id."/".$fileName;
            //delete old file
            File::delete(storage_path('app/public/banner-viewer/server-'.$banner->server_id."/".$banner->banner_viewer_file_name));

        }else
        {
            //get storage path
            $storagePath = storage_path('app/public/banner-viewer/server-'.$banner->server_id);
            $file = storage_path('app/public/banner-viewer/server-'.$banner->server_id."/".$fileName);
            $storagePathAsset = 'storage/banner-viewer/server-'.$banner->server_id."/".$fileName;

            if(File::exists($storagePath) == false)
            {
                File::makeDirectory($storagePath,0777,true,true);
            }
        }

        //set header type
        header('Content-Type:image/png');
        //create banner
        $successCreated = imagepng($img,$file);
        imagedestroy($img);

        if($successCreated == true)
        {
            //update banner
            banner::query()->where('id','=',$request->input('bannerID'))->update([
                'banner_viewer'=>$storagePathAsset,
                'banner_viewer_file_name'=>$fileName,
                'banner_hostbanner_url'=>$request->input('BannerUrl'),
                'delay'=>$request->input('RotationTimeDelay'),
                'next_check_at'=>Carbon::now()->addMinutes($request->input('RotationTimeDelay')),
            ]);
        }

        return 0;
    }

    private function deleteBanner($bannerID)
    {
        //delete banner options
        bannerOption::query()->where('banner_id','=',$bannerID)->delete();

        //get banner
        $banner = banner::query()->where('id','=',$bannerID)->first();
        $serverID = $banner->server_id;

        //delete files
        if(File::exists(storage_path('app/public/banner-template/server-'.$banner->server_id.'/'.$banner->banner_original_file_name)))
        {
            File::delete(storage_path('app/public/banner-template/server-'.$banner->server_id.'/'.$banner->banner_original_file_name));
        }
        //delete files
        if(File::exists(storage_path('app/public/banner-viewer/server-'.$banner->server_id.'/'.$banner->banner_viewer_file_name)))
        {
            File::delete(storage_path('app/public/banner-viewer/server-'.$banner->server_id.'/'.$banner->banner_viewer_file_name));
        }

        banner::query()->where('id','=',$bannerID)->delete();

        return $serverID;
    }
}
