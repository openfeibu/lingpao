<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("user.name") }}</cite></a>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('user/'.$user->id)}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('user.name')!!}：</label>
                        <div class="layui-input-block">
                            <p class="input-p">
                                <img src="{{ $user->avatar_url }}" class="avatar">
                                <span>{{ $user->nickname }} {{ $user->phone }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.balance')!!}：</label>
                        <div class="layui-input-block">
                            <p class="input-p">
                                {{ $user->balance }}
                            </p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ trans("user.label.roles") }}</label>
                        <div class="layui-input-inline">
                            <select name="role" class="search_key">
                            @foreach(config('common.user.roles') as $key => $role)
                                <option value="{{ $role }}" @if($role == $user->role) selected @endif>{{ trans('user.roles.'.$role) }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                        </div>
                    </div>
                    {!!Form::token()!!}
                    <input type="hidden" name="_method" value="PUT">
                </form>
            </div>

        </div>
    </div>
</div>
<script>
    layui.use('form', function(){
        var form = layui.form;

        form.render();
    });
</script>

