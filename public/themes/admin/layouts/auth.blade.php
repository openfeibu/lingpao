<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{!! Theme::getTitle() !!} :: {{trans('app.name')}}</title>
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
	{!! Theme::asset()->styles() !!}
     {{--<script src='{{ asset('js/jquery-1.7.2.min.js') }}'></script>--}}
    {!! Theme::asset()->scripts() !!}
    {!! Theme::asset()->container('footer')->scripts() !!}

</head>
<body class="login-bg">
{!! Theme::content() !!}
</body>
</html>