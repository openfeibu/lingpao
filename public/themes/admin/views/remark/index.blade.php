<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("remark.name") }}管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            {{--<div class="tabel-message layui-form">--}}
                {{--<div class="layui-inline">--}}
                    {{--<input class="layui-input search_key" name="search_name" id="demoReload" placeholder="昵称/手机号" autocomplete="off" value="{{ $search_name }}">--}}
                {{--</div>--}}
                {{--<button class="layui-btn" data-type="reload">搜索</button>--}}
            {{--</div>--}}

            <table id="fb-table" class="layui-table"  lay-filter="fb-table">

            </table>
        </div>
    </div>
</div>


<script type="text/html" id="imageTEM">
    <img src="@{{d.avatar_url}}" alt="" height="28">
</script>
<script type="text/html" id="userTEM">
    <img src="@{{d.avatar_url}}" alt="" height="28"> <span>@{{d.nickname}}</span>
</script>
<script type="text/html" id="delivererTEM">
    @{{#  if(d.deliverer_id){ }}
    <img src="@{{d.deliverer_avatar_url}}" alt="" height="28"><span>@{{d.deliverer_nickname}}</span>
    @{{#  } }}
</script>

<script>
    var main_url = "{{guard_url('remark')}}";
    var delete_all_url = "{{guard_url('remark/destroyAll')}}";
    layui.use(['jquery','element','table'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('remark')}}@if($deliverer_id)?deliverer_id={{$deliverer_id}}@endif'
            ,cols: [[
                {checkbox: true, fixed: true}
                ,{field:'id',title:'ID', width:80, sort: true}
                ,{field:'deliverer_detail',title:'{!! trans('task_order.label.deliverer')!!}',toolbar:'#delivererTEM'}
                ,{field:'user_detail',title:'{!! trans('task_order.label.user')!!}',toolbar:'#userTEM'}
                ,{field:'service_grade',title:'{!! trans('remark.label.service_grade')!!}'}
                ,{field:'speed_grade',title:'{!! trans('remark.label.speed_grade')!!}'}
                ,{field:'comment',title:'{!! trans('remark.label.comment')!!}'}
                ,{field:'created_at',title:'{!! trans('app.created_at')!!}'}
            ]]
            ,id: 'fb-table'
            ,page: true
            ,limit: '{{ config('app.limit') }}'
            ,height: 'full-200'
        });


    });
</script>
{!! Theme::partial('common_handle_js') !!}