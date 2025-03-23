@extends('template')

@section('site-title')
    Banner Creator | {{ config('app.project') }}
@endsection

@section('content')
    <div class="container mt-3" xmlns="http://www.w3.org/1999/html">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Banner Creator | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h1>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-lg-auto">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#BannerUpload">
                    Banner Template hinzufügen
                </button>
            </div>
            @include('backend.banner-creator.inc.banner-upload')
        </div>
        <hr>
        @include('form-components.alertCustomError')
        @include('form-components.successCustom')
        @if($banners->count() == 0)
            <div class="row">
                <div class="alert alert-warning" role="alert">
                    Es wurden noch keine Templates gefunden
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-lg-3 g-2">
                @foreach($banners as $banner)
                    <div class="col">
                        <div class="card">
                            <a href="{{Route('banner.view.configBanner',['bannerID'=>$banner->id])}}">
                                @if($banner->banner_viewer == null)
                                    <img src="{{asset('banner/'. $banner->banner_original)}}" class="img-thumbnail"
                                         alt="Banner Templates">
                                @else
                                    <img src="{{asset('banner/'.$banner->banner_viewer)}}" class="img-thumbnail"
                                         alt="Banner Templates">
                                @endif
                            </a>
                            <button class="btn btn-link btn-primary text-danger" type="button" data-bs-toggle="modal" data-bs-target="#BannerDelete{{$banner->id}}"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
            @foreach($banners as $banner)
                @include('backend.banner-creator.inc.banner-delete', ['banner'=>$banner])
            @endforeach
        @endif
    </div>
@endsection
