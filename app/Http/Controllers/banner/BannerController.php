<?php

namespace App\Http\Controllers\banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\CreateUploadedTemplateRequest;
use App\Http\Requests\Banner\DeleteBannerRequest;
use App\Http\Requests\Banner\UpsertConfigBannerRequest;
use App\Http\Requests\Banner\ViewConfigBannerRequest;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function viewListBanner(ViewListBannerRequest $request): View|Factory|RedirectResponse|Application
    {
        $banners = banner::query()->where('server_id', '=', $request->validated('server_id'))->get();

        return view('backend.banner-creator.banner-list')->with([
            'banners'=>$banners,
        ]);
    }

    public function viewConfigBanner(ViewConfigBannerRequest $request): View|Factory|RedirectResponse|Application
    {
        $banner = banner::query()->where('id', '=', $request->validated('id'))->first();
        $bannerOptions = catBannerOption::query()->get();
        $bannerFonts = catFont::query()->get();
        $serverGroups = ts3ServerGroup::query()
            ->where('server_id', '=', $banner->server_id)
            ->where('type', '=', 1)
            ->get();
        $storedBannerOptions = bannerOption::query()
            ->where('banner_id', '=', $request->validated('id'))->get();

        if ($storedBannerOptions->count() != 0) {
            $optionsAvailable = true;
        } else {
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

    public function createUploadedTemplate(CreateUploadedTemplateRequest $request): RedirectResponse
    {
        $uploaded = Storage::disk('banner')->putFileAs('template', $request->file('banner_original_file_name'), $request->file('banner_original_file_name')->getClientOriginalName());
        if ($uploaded === false) {
            return redirect()->back()->withErrors(['errors'=>'Unable to create banner original image.']);
        }

        $templateExists = banner::query()->where('banner_original_file_name', '=', $request->file('banner_original_file_name')->getClientOriginalName())->exists();
        if ($templateExists == true) {
            return redirect()->back()->withErrors(['errors'=>'Template file already exists.']);
        }

        banner::query()->create([
            'server_id'=>$request->validated('server_id'),
            'banner_name'=>$request->validated('banner_name'),
            'banner_original_file_name'=>$request->file('banner_original_file_name')->getClientOriginalName(),
            'delay'=>1,
            'next_check_at'=>Carbon::now(),
        ]);

        return redirect()->route('banner.view.listBanner')->with('success', 'Banner created successfully');
    }

    public function upsertConfigBanner(UpsertConfigBannerRequest $request): RedirectResponse
    {
        $banner = banner::query()->where('id', '=', $request->validated('id'))->first();

        if (Storage::disk('banner')->exists('template/'.$banner->banner_original_file_name) == false) {
            return redirect()->back()->withErrors(['errors'=>'Template file could not be found.']);
        }
        $img = imagecreatefrompng(Storage::disk('banner')->path('template/'.$banner->banner_original_file_name));

        //change color form hex to rgb
        list($r, $g, $b) = sscanf($request->validated('color_hex'), '#%02x%02x%02x');
        $fontColor = imagecolorallocate($img, $r, $g, $b);
        $fontStyle = Storage::disk('fonts')->path(catFont::query()->where('id', '=', $request->validated('font_id'))->first()->font_name);

        //get options
        $countBannerOption1 = collect($request->validated('option_id'))->count();
        $bannerOption1 = collect($request->validated('option_id'));
        $bannerOption2 = collect($request->validated('extra_option'));
        $coordXs = collect($request->validated('coord_x'));
        $coordYs = collect($request->validated('coord_y'));
        $texts = collect($request->validated('text'));
        $availableBannerOptions = catBannerOption::query()->get(['id', 'pes_code']);

        //delete all current options
        bannerOption::query()
            ->where('banner_id', '=', $request->validated('id'))
            ->delete();

        for ($i = 0; $i < $countBannerOption1; $i++) {
            if ($bannerOption1[$i] != 'delete' && $availableBannerOptions->where('id', '=', $bannerOption1[$i])->first()->pes_code != 'get_no_options') {
                $text = '0';

                if ($texts[$i] != null && $availableBannerOptions->where('id', '=', $bannerOption1[$i])->first()->pes_code == 'get_text') {
                    $text = $texts[$i];
                }

                imagettftext($img, $request->validated('font_size'), 0, $coordXs[$i], $coordYs[$i], $fontColor, $fontStyle, $text);

                bannerOption::query()->create([
                    'banner_id'=>$request->validated('id'),
                    'font_id'=>$request->validated('font_id'),
                    'font_size'=>$request->validated('font_size'),
                    'color_hex'=>$request->validated('color_hex'),
                    'option_id'=>$bannerOption1[$i],
                    'extra_option'=>$bannerOption2[$i],
                    'text'=>$text,
                    'coord_x'=>$coordXs[$i],
                    'coord_y'=>$coordYs[$i],
                ]);
            }
        }

        if ($banner->banner_viewer_file_name != null && Storage::disk('banner')->exists('viewer/'.$banner->banner_viewer_file_name)) {
            $fileName = $banner->banner_viewer_file_name;
        } else {
            $fileName = $banner->server_id.'-'.uniqid().'.png';
        }

        $filePath = Storage::disk('banner')->path('viewer/'.$fileName);

        header('Content-Type:image/png');
        $successCreated = imagepng($img, $filePath);
        imagedestroy($img);

        if ($successCreated == true) {
            banner::query()->where('id', '=', $request->validated('id'))->update([
                'banner_viewer_file_name'=>$fileName,
                'banner_hostbanner_url'=>$request->validated('banner_hostbanner_url'),
                'delay'=>$request->validated('delay'),
                'next_check_at'=>Carbon::now()->addMinutes($request->validated('delay')),
            ]);

            return redirect()->back()->with('success', 'Configuration successfully updated.');
        } else {
            return redirect()->back()->with('errors', 'Create Viewer Image failed. Please try again.');
        }
    }

    public function deleteBanner(DeleteBannerRequest $request): RedirectResponse
    {
        $banner = banner::query()->where('id', '=', $request->validated('id'))->first();

        if (Storage::disk('banner')->exists('template/'.$banner->banner_original_file_name)) {
            Storage::disk('banner')->delete('template/'.$banner->banner_original_file_name);
        }

        if (Storage::disk('banner')->exists('viewer/'.$banner->banner_viewer_file_name)) {
            Storage::disk('banner')->delete('viewer/'.$banner->banner_viewer_file_name);
        }

        bannerOption::query()->where('banner_id', '=', $request->validated('id'))->delete();
        banner::query()->where('id', '=', $request->validated('id'))->delete();

        return redirect()->back()->with(['success'=>'Banner was successfully deleted']);
    }
}
