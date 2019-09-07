<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("user.name") }}管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="tabel-message layui-form">
                <div class="layui-inline">
                    <label class="layui-form-label">{!! trans('user.label.roles')!!}</label>
                    <div class="layui-input-block">
                        <select name="role" class="search_key">
                            <option value="">全部</option>
                            @foreach(config('common.user.roles') as $key => $role)
                                <option value="{{ $key }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <input class="layui-input search_key" name="search_name" id="demoReload" placeholder="ID/昵称/手机号" autocomplete="off">
                </div>
                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table id="fb-table" class="layui-table"  lay-filter="fb-table">

            </table>
        </div>
    </div>
</div>

<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-sm" target="_blank" href="{{ guard_url('balance_record?user_id=') }}@{{d.id}}">钱包明细</a>
    <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
</script>
<script type="text/html" id="imageTEM">
    <img src="@{{d.avatar_url}}" alt="" height="28">
</script>

<script>
    var main_url = "{{guard_url('user')}}";
    var delete_all_url = "{{guard_url('user/destroyAll')}}";
    layui.use(['jquery','element','table'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('user')}}'
            ,cols: [[
                {checkbox: true, fixed: true}
                ,{field:'id',title:'ID', width:80, sort: true}
                ,{field:'nickname',title:'{!! trans('user.label.nickname')!!}'}
                ,{field:'avatar_url',title:'{!! trans('user.label.avatar_url')!!}',toolbar:'#imageTEM'}
                ,{field:'phone',title:'{!! trans('user.label.phone')!!}'}
                ,{field:'role_name',title:'{!! trans('user.label.roles')!!}'}
                ,{field:'score',title:'操作', width:200, align: 'right',toolbar:'#barDemo'}
            ]]
            ,id: 'fb-table'
            ,page: true
            ,limit: '{{ config('app.limit') }}'
            ,height: 'full-200'
        });


    });
</script>
{!! Theme::partial('common_handle_js') !!}