<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('site-title')</title>

    <link href="{{asset('/css/bootstrap/bootstrap.min.css')}}" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('storage/img/bot-logo.png')}}">

    @yield('custom-css')
</head>
<body class="d-flex flex-column vh-100">
@include('navs.main-nav')

@yield('content')

@include('navs.main-footer')
@yield('js-footer')
<script src="{{asset('/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
