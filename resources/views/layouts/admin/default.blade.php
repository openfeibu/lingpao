<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="screen-orientation" content="portrait">
    <meta name="x5-orientation" content="portrait">
    <title>{!! Theme::getTitle() !!} :: {{trans('app.name')}}</title>
    <link rel="stylesheet" href="{{ asset('layui/css/layui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/main.css') }}">
    <!-- <script src='{{ asset('js/jquery-1.7.2.min.js') }}'></script> -->
</head>
<body>
    @section('header')
        @include('admin.partials.header')
    @show
    @section('aside')
        @include('admin.partials.aside')
    @show
    @yield('content')
    <script src='{{ asset('layui/layui.js') }}'></script>
    <script src='{{ asset('js/admin/main.js') }}'></script>
    @section('footer')
        @include('admin.partials.footer')
    @show
    @section('footer_is')
    @endsection
</body>
</html>
