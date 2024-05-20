@extends('template')

@section('site-title')
    Banner Creator | PS-Bot
@endsection

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">Banner Creator</h1>
        </div>
    </div>
    <hr>
    <div class="row mb-3">
        <form method="post" action="{{Route('banner.create.uploadedBanner')}}" enctype="multipart/form-data">
            @csrf
            <div class="col-lg-auto">
                <a href="#BannerUpload" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#BannerUpload">Banner hinzufügen</a>
            </div>
            @include('inc.bot-config.banner-upload')
        </form>
    </div>
    @include('form-components/alertCustomError')
    <div class="row row-cols-1 row-cols-lg-3 g-2">
        @foreach($banners as $banner)
            <div class="col">
                <div class="card">
                    <a href="{{Route('banner.view.createBanner',['bannerID'=>$banner->id])}}">
                        @if($banner->banner_viewer == NULL)
                            <img src="{{asset($banner->banner_original)}}" class="img-thumbnail" alt="Banner Templates">
                        @else
                            <img src="{{asset($banner->banner_viewer)}}" class="img-thumbnail" alt="Banner Templates">
                        @endif
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection