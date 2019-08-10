<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="screen-orientation" content="portrait">
    <meta name="x5-orientation" content="portrait">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{!! Theme::getTitle() !!} :: {{trans('app.name')}}</title>
    {!! Theme::asset()->styles() !!}
    {{--<script src='{{ asset('js/jquery-1.7.2.min.js') }}'></script>--}}
    {!! Theme::asset()->scripts() !!}
    {!! Theme::asset()->container('footer')->scripts() !!}
</head>
<body>
    {!! Theme::partial('header') !!}
    {!! Theme::partial('aside') !!}
    {!! Theme::content() !!}
    {!! Theme::partial('footer') !!}
</body>
</html>
