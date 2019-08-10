@if (Session::has('status')  && Session::has('message'))
    @if(Session::get('status') == 'success')
        <div class="layui-alert layui-bg-gray">
            <button type="button" class="layui-close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>{{ Session::get('message') }}</strong>
        </div>
    @elseif(Session::get('status') == 'error')
        <div class="layui-alert layui-bg-red">
            <button type="button" class="layui-close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>{{ Session::get('message') }}</strong>
        </div>
    @endif
@endif


@if(isset($errors) && $errors->all())
    @foreach($errors->all() as $message)
        <div class="layui-alert layui-bg-red">
            <button type="button" class="layui-close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>{{ $message }}</strong>
        </div>
    @endforeach
@endif