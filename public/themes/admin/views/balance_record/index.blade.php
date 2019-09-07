<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("balance_record.name") }}管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="tabel-message layui-form">
                <div class="layui-inline">
                    <input class="layui-input search_key" name="search_name" id="demoReload" placeholder="昵称/手机号" autocomplete="off" value="{{ $search_name }}">
                </div>
                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table id="fb-table" class="layui-table"  lay-filter="fb-table">

            </table>
        </div>
    </div>
</div>


<script type="text/html" id="imageTEM">
    <img src="@{{d.avatar_url}}" alt="" height="28">
</script>

<script>
    var main_url = "{{guard_url('balance_record')}}";
    var delete_all_url = "{{guard_url('balance_record/destroyAll')}}";
    layui.use(['jquery','element','table'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('balance_record')}}@if($user_id)?user_id={{$user_id}}@endif'
            ,cols: [[
                {checkbox: true, fixed: true}
                ,{field:'id',title:'ID', width:80, sort: true}
                ,{field:'nickname',title:'{!! trans('user.label.nickname')!!}'}
                ,{field:'phone',title:'{!! trans('user.label.phone')!!}'}
                ,{field:'avatar_url',title:'{!! trans('user.label.avatar_url')!!}',toolbar:'#imageTEM'}
                ,{field:'description',title:'{!! trans('balance_record.label.description')!!}'}
                ,{field:'price_desc',title:'{!! trans('balance_record.label.price')!!}'}
                ,{field:'fee',title:'{!! trans('app.fee')!!}'}
                ,{field:'balance',title:'{!! trans('balance_record.label.balance')!!}'}
            ]]
            ,id: 'fb-table'
            ,page: true
            ,limit: '{{ config('app.limit') }}'
            ,height: 'full-200'
        });


    });
</script>
{!! Theme::partial('common_handle_js') !!}