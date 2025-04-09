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
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <button type="button"
                                class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5"
                                data-bs-toggle="modal"
                                data-bs-target="#BannerUpload">
                            <i class="fa-solid fa-circle-plus"></i> Add Banner Template
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        @include('form-components.alertCustomError')
        @include('form-components.successCustom')
        @if($banners->count() == 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="alert alert-primary" role="alert">
                        No templates have been found yet
                    </div>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-lg-3 g-2">
                @foreach($banners as $banner)
                    <div class="col">
                        <div class="card">
                            <a href="{{Route('banner.view.configBanner',['id'=>$banner->id])}}">
                                @empty($banner->banner_viewer_file_name)
                                    <img src="{{asset('banner/template/'. $banner->banner_original_file_name)}}" class="img-thumbnail"
                                         alt="Banner Templates">
                                @else
                                    <img src="{{asset('banner/viewer/'.$banner->banner_viewer_file_name)}}" class="img-thumbnail"
                                         alt="Banner Templates">
                                @endif
                            </a>
                            <button class="btn btn-link btn-primary text-danger" type="button" data-bs-toggle="modal" data-bs-target="#BannerDelete{{$banner->id}}"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@include('backend.banner-creator.inc.banner-upload')

@foreach($banners as $banner)
    @include('backend.banner-creator.inc.banner-delete', ['banner'=>$banner])
@endforeach
@endsection
