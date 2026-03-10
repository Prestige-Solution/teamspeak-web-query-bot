<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('site-title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('storage/img/bot-logo.png')}}">
    @yield('custom-css')
</head>
<body>
    <div class="d-flex flex-column vh-100">
    @include('navs.main-nav')

    @yield('content')

    @include('navs.main-footer')
    @yield('js-footer')
    </div>
</body>
</html>
