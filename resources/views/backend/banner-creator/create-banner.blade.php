@extends('template')

@section('site-title')
    Create Banner | PS-Bot
@endsection

@section('js-footer')
    <script src="{{asset('js/pes-custom-js/banner-creator.js')}}" type="text/javascript"></script>
@endsection

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-2 fw-bold">Banner Creator</h1>
        </div>
    </div>
    <hr>
    <form method="get" action="{{Route('banner.update.updateBanner')}}">
        <div class="row mb-3">
            <div class="col-lg-6">
                <label class="form-label fw-bold">Vorlage:</label>
                <img class="img-thumbnail" id="BannerImage" src="{{asset($banner->banner_original)}}" alt="BannerImage" onclick="imageCoordinates()">
            </div>
            <div class="col-lg-6">
                <label class="form-label fw-bold">Preview:</label>
                @if($banner->banner_viewer != NULL)
                    <img class="img-thumbnail" id="BannerImagePreview" src="{{asset($banner->banner_viewer)}}" alt="BannerImagePreview">
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="Xcoord">X-Coordinate (Klick Vorlage):</label>
                <input class="form-control" type="number" name="Xcoord" id="Xcoord" readonly>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="Ycoord">Y-Coordinate (Klick Vorlage):</label>
                <input class="form-control" type="number" name="Ycoord" id="Ycoord" readonly>
            </div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-lg-auto">
                <h2 class="fs-4 fw-bold">Banner Optionen</h2>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="FontOption">Schriftart auswählen:</label>
                <select class="form-select" name="FontOption" id="FontOption">
                    @foreach($bannerFonts as $bannerFont)
                        <option value="{{$bannerFont->id}}" @if($optionsStored == true) @if($storedBannerOptions->first()->font_id == $bannerFont->id) selected @endif @endif>{{$bannerFont->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="FontSize">Schriftgröße wählen:</label>
                <input class="form-control" type="number" name="FontSize" id="FontSize" min="1" @if($optionsStored == true)value="{{$storedBannerOptions->first()->font_size}}" @else value="1" @endif>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="ColorSelect">Schriftfarbe wählen:</label>
                <input class="form-control" type="color" name="ColorSelect" id="ColorSelect" @if($optionsStored == true) value="{{$storedBannerOptions->first()->color_hex}}" @endif>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-6">
                <label class="form-label fw-bold" for="BannerUrl">Banner URL:</label>
                <input class="form-control" type="url" name="BannerUrl" id="BannerUrl" placeholder="https://meine-domain.de" @if($optionsStored == true) value="{{$banner->banner_hostbanner_url}}" @endif>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-bold" for="RotationTimeDelay">Verzögerung in Minuten:</label>
                <input class="form-control" type="number" name="RotationTimeDelay" id="RotationTimeDelay" min="1" @if($optionsStored == true) value="{{$banner->delay}}" @else value="1" @endif>
            </div>
        </div>
        <hr>
        <div class="row d-none d-md-flex">
            <div class="col-lg-3">
                <p class="mb-0 fw-bold">Option wählen</p>
            </div>
            <div class="col-lg-3">
                <p class="mb-0 fw-bold">Zusatz wählen</p>
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
                            <select class="form-select" name="BannerOption1[]" id="BannerOption1" aria-label="Banner Option 1">
                                <option disabled>--- Standard ---</option>
                                @foreach($bannerOptions->where('category','=','no_options') as $bannerOptionNoOption)
                                    <option value="{{$bannerOptionNoOption->id}}" @if($bannerOptionNoOption->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionNoOption->name}}</option>
                                    <option value="delete">Option löschen</option>
                                @endforeach
                                <option disabled>--- Server ---</option>
                                @foreach($bannerOptions->where('category','=','server') as $bannerOptionServer)
                                    <option value="{{$bannerOptionServer->id}}" @if($bannerOptionServer->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionServer->name}}</option>
                                @endforeach
                                <option disabled>--- Servergruppen ---</option>
                                @foreach($bannerOptions->where('category','=','server_groups') as $bannerOptionServerGroups)
                                    <option value="{{$bannerOptionServerGroups->id}}" @if($bannerOptionServerGroups->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionServerGroups->name}}</option>
                                @endforeach
                                <option disabled>--- Beschreibung ---</option>
                                @foreach($bannerOptions->where('category','=','text') as $bannerOptionText)
                                    <option value="{{$bannerOptionText->id}}" @if($bannerOptionText->id == $storedBannerOption->option_id) selected @endif>{{$bannerOptionText->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <select class="form-select" name="BannerOption2[]" id="BannerOption2" aria-label="Banner Option 2">
                                <option disabled>--- Virtueller Server ---</option>
                                <option value="0" selected >Keine Extra Option</option>
                                <option disabled>--- Servergruppen ---</option>
                                @foreach($serverGroups as $serverGroup)
                                    <option value="{{$serverGroup->sgid}}" @if($serverGroup->sgid == $storedBannerOption->extra_option) selected @endif>{{$serverGroup->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <input class="form-control" type="text" name="Text[]" id="Text" aria-label="Text" placeholder="Nur wenn Option = Text" value="@if($storedBannerOption->text != 0) {{$storedBannerOption->text}} @endif">
                        </div>
                        <div class="col-lg-1 mb-2">
                            <input type="number" class="form-control" name="CoordX[]" id="CoordX" placeholder="X" aria-label="X" value="{{$storedBannerOption->coord_x}}">
                        </div>
                        <div class="col-lg-1 mb-2">
                            <input type="number" class="form-control" name="CoordY[]" id="CoordY" placeholder="Y" aria-label="Y" value="{{$storedBannerOption->coord_y}}">
                        </div>
                    </div>
                @endforeach
                <div class="row mt-3" id="BannerOptionGroup">
                    <div class="col-lg-3 mb-2">
                        <select class="form-select" name="BannerOption1[]" id="BannerOption1" aria-label="Banner Option 2">
                            <option disabled>--- Standard ---</option>
                            @foreach($bannerOptions->where('category','=','no_options') as $bannerOptionNoOption)
                                <option value="{{$bannerOptionNoOption->id}}">{{$bannerOptionNoOption->name}}</option>
                                <option value="delete">Option löschen</option>
                            @endforeach
                            <option disabled>--- Server ---</option>
                            @foreach($bannerOptions->where('category','=','server') as $bannerOptionServer)
                                <option value="{{$bannerOptionServer->id}}">{{$bannerOptionServer->name}}</option>
                            @endforeach
                                <option disabled>--- Servergruppen ---</option>
                            @foreach($bannerOptions->where('category','=','server_groups') as $bannerOptionServerGroups)
                                <option value="{{$bannerOptionServerGroups->id}}">{{$bannerOptionServerGroups->name}}</option>
                            @endforeach
                            <option disabled>--- Beschreibung ---</option>
                            @foreach($bannerOptions->where('category','=','text') as $bannerOptionText)
                                <option value="{{$bannerOptionText->id}}">{{$bannerOptionText->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <select class="form-select" name="BannerOption2[]" id="BannerOption2" aria-label="Banner Option 2">
                            <option disabled>--- Virtueller Server ---</option>
                            <option value="0" selected >Keine Extra Option</option>
                            <option disabled>--- Servergruppen ---</option>
                            @foreach($serverGroups as $serverGroup)
                                <option value="{{$serverGroup->sgid}}">{{$serverGroup->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <input class="form-control" type="text" name="Text[]" id="Text" aria-label="Text" placeholder="Nur wenn Option = Text">
                    </div>
                    <div class="col-lg-1 mb-2">
                        <input type="number" class="form-control" name="CoordX[]" id="CoordX" placeholder="X" aria-label="X">
                    </div>
                    <div class="col-lg-1 mb-2">
                        <input type="number" class="form-control" name="CoordY[]" id="CoordY" placeholder="Y" aria-label="Y">
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
                    <button class="btn btn-primary" name="bannerID" value="{{$banner->id}}">Speichern</button>
                </div>
                <div class="col-lg-auto">
                    <button class="btn btn-danger" name="DeleteBannerID" value="{{$banner->id}}">Löschen</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection