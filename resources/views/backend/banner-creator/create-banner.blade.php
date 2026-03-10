@extends('template')

@section('site-title')
    Banner Configuration | {{ config('app.project') }}
@endsection

@section('js-footer')
    <script src="{{asset('js/pes-custom-js/banner-creator.js')}}" type="text/javascript"></script>
@endsection

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-2 fw-bold">Banner Configuration | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h1>
        </div>
    </div>
    <hr>
    @include('form-components.successCustom')
    @include('form-components.alertCustomError')
    <form method="post" action="{{Route('banner.upsert.configBanner')}}">
        @csrf
        <div class="row mb-3">
            <div class="col-lg-6">
                <label class="form-label fw-bold">Template:</label>
                <img class="img-thumbnail" id="BannerImage" src="{{asset('banner/template/'.$banner->banner_original_file_name)}}" alt="BannerImage" onclick="imageCoordinates()">
            </div>
            <div class="col-lg-6">
                <label class="form-label fw-bold">Preview:</label>
                @if($banner->banner_viewer_file_name !== null)
                    <img class="img-thumbnail" id="BannerImagePreview" src="{{asset('banner/viewer/'.$banner->banner_viewer_file_name)}}" alt="BannerImagePreview">
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="coord_x">X-Coordinate (Click template):</label>
                <input class="form-control" type="number" name="coord_x" id="coord_x" readonly>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="coord_y">Y-Coordinate (Click template):</label>
                <input class="form-control" type="number" name="coord_y" id="coord_y" readonly>
            </div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-lg-auto">
                <h2 class="fs-4 fw-bold">Banner options</h2>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="font_id">Font:</label>
                <select class="form-select" name="font_id" id="font_id">
                    @foreach($bannerFonts as $bannerFont)
                        <option value="{{$bannerFont->id}}" @if($optionsStored == true) @if($storedBannerOptions->first()->font_id == $bannerFont->id) selected @endif @endif>{{$bannerFont->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="font_size">Font size:</label>
                <input class="form-control" type="number" name="font_size" id="font_size" min="1" @if($optionsStored == true)value="{{$storedBannerOptions->first()->font_size}}" @else value="1" @endif>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="color_hex">Font color:</label>
                <input class="form-control" type="color" name="color_hex" id="color_hex" @if($optionsStored == true) value="{{$storedBannerOptions->first()->color_hex}}" @endif>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="delay">Delay in minutes:</label>
                <input class="form-control" type="number" name="delay" id="delay" min="1" @if($optionsStored == true) value="{{$banner->delay}}" @else value="1" @endif>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-6">
                <label class="form-label fw-bold" for="banner_hostbanner_url">Banner URL:</label>
                <input class="form-control" type="url" name="banner_hostbanner_url" id="banner_hostbanner_url" placeholder="https://meine-domain.de" @if($optionsStored == true) value="{{$banner->banner_hostbanner_url}}" @endif>
                <div id="banner_hostbanner_url_help" class="form-text">
                    Enter a URL where the user should redirect if they click the banner
                </div>
            </div>
        </div>
        <hr>
        <div class="row d-none d-md-flex">
            <div class="col-lg-3">
                <p class="mb-0 fw-bold">Option</p>
            </div>
            <div class="col-lg-3">
                <p class="mb-0 fw-bold">Extra</p>
            </div>
            <div class="col-lg-3">
                <p class="mb-0 fw-bold">Text</p>
            </div>
            <div class="col-lg-1">
                <p class="mb-0 fw-bold">Pos-X</p>
            </div>
            <div class="col-lg-1">
                <p class="mb-0 fw-bold">Pos-Y</p>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-lg-12">
                @foreach($storedBannerOptions as $storedBannerOption)
                    <div class="row mt-3" id="BannerOptionGroup">
                        <div class="col-lg-3 mb-2">
                            <select class="form-select" name="option_id[]" id="option_id" aria-label="option_id">
                                <option disabled>--- Default ---</option>
                                @foreach($bannerOptions->where('category','=','no_options') as $bannerOptionNoOption)
                                    <option value="{{$bannerOptionNoOption->id}}" @if($bannerOptionNoOption->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionNoOption->name}}</option>
                                    <option value="delete">Remove option</option>
                                @endforeach
                                <option disabled>--- Server ---</option>
                                @foreach($bannerOptions->where('category','=','server') as $bannerOptionServer)
                                    <option value="{{$bannerOptionServer->id}}" @if($bannerOptionServer->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionServer->name}}</option>
                                @endforeach
                                <option disabled>--- Servergroups ---</option>
                                @foreach($bannerOptions->where('category','=','server_groups') as $bannerOptionServerGroups)
                                    <option value="{{$bannerOptionServerGroups->id}}" @if($bannerOptionServerGroups->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionServerGroups->name}}</option>
                                @endforeach
                                <option disabled>--- Description ---</option>
                                @foreach($bannerOptions->where('category','=','text') as $bannerOptionText)
                                    <option value="{{$bannerOptionText->id}}" @if($bannerOptionText->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionText->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <select class="form-select" name="extra_option[]" id="extra_option" aria-label="extra_option">
                                <option value="0" selected >No extra option</option>
                                <option disabled>--- Servergroup ---</option>
                                @foreach($serverGroups as $serverGroup)
                                    <option value="{{$serverGroup->sgid}}" @if($serverGroup->sgid == $storedBannerOption->extra_option) selected @endif>{{$serverGroup->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <input class="form-control" type="text" name="text[]" id="text" aria-label="Text" placeholder="Nur wenn Option = Text" value="@if($storedBannerOption->text != 0) {{$storedBannerOption->text}} @endif">
                        </div>
                        <div class="col-lg-1 mb-2">
                            <input type="number" class="form-control" name="coord_x[]" id="coord_x" placeholder="X" aria-label="X" value="{{$storedBannerOption->coord_x}}">
                        </div>
                        <div class="col-lg-1 mb-2">
                            <input type="number" class="form-control" name="coord_y[]" id="coord_y" placeholder="Y" aria-label="Y" value="{{$storedBannerOption->coord_y}}">
                        </div>
                    </div>
                @endforeach
                <div class="row mt-3" id="BannerOptionGroup">
                    <div class="col-lg-3 mb-2">
                        <select class="form-select" name="option_id[]" id="option_id" aria-label="option_id">
                            <option disabled>--- Default ---</option>
                            @foreach($bannerOptions->where('category','=','no_options') as $bannerOptionNoOption)
                                <option value="{{$bannerOptionNoOption->id}}">{{$bannerOptionNoOption->name}}</option>
                                <option value="delete">Remove option</option>
                            @endforeach
                            <option disabled>--- Server ---</option>
                            @foreach($bannerOptions->where('category','=','server') as $bannerOptionServer)
                                <option value="{{$bannerOptionServer->id}}">{{$bannerOptionServer->name}}</option>
                            @endforeach
                                <option disabled>--- Servergroups ---</option>
                            @foreach($bannerOptions->where('category','=','server_groups') as $bannerOptionServerGroups)
                                <option value="{{$bannerOptionServerGroups->id}}">{{$bannerOptionServerGroups->name}}</option>
                            @endforeach
                            <option disabled>--- Description ---</option>
                            @foreach($bannerOptions->where('category','=','text') as $bannerOptionText)
                                <option value="{{$bannerOptionText->id}}">{{$bannerOptionText->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <select class="form-select" name="extra_option[]" id="extra_option" aria-label="extra_option">
                            <option value="0" selected >No extra option</option>
                            <option disabled>--- Servergroup ---</option>
                            @foreach($serverGroups as $serverGroup)
                                <option value="{{$serverGroup->sgid}}">{{$serverGroup->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <input class="form-control" type="text" name="text[]" id="text" aria-label="text" placeholder="Nur wenn Option = Text">
                    </div>
                    <div class="col-lg-1 mb-2">
                        <input type="number" class="form-control" name="coord_x[]" id="coord_x" placeholder="X" aria-label="X">
                    </div>
                    <div class="col-lg-1 mb-2">
                        <input type="number" class="form-control" name="coord_y[]" id="coord_y" placeholder="Y" aria-label="Y">
                    </div>
                </div>
                <div class="row justify-content-lg-center mt-3 mb-3" id="AddOptionGroupButton">
                    <div class="col-lg-1">
                        <span class="btn rounded-circle btn-success" onclick="addBannerOptionGroup()">+</span>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-lg-auto">
                    <button class="btn btn-primary" name="id" value="{{$banner->id}}">Speichern</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
