{!! Theme::asset()->styles() !!}
{{--<script src='{{ asset('js/jquery-1.7.2.min.js') }}'></script>--}}
{!! Theme::asset()->scripts() !!}
{!! Theme::asset()->container('footer')->scripts() !!}
<body>
{!! Theme::content() !!}
</body>