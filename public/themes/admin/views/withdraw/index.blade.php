<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("withdraw.name") }}管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="tabel-message layui-form">
                <div class="layui-inline">
                    <input class="layui-input search_key" name="search_name" id="demoReload" placeholder="昵称/手机号" autocomplete="off" value="">
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

<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-sm" lay-event="withdraw_pass">支付</a>
    <a class="layui-btn layui-btn-sm" lay-event="withdraw_reject">驳回</a>
</script>

<script>
    var main_url = "{{guard_url('withdraw')}}";
    var delete_all_url = "{{guard_url('withdraw/destroyAll')}}";
    layui.use(['jquery','element','table'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('withdraw')}}'
            ,cols: [[
                {checkbox: true, fixed: true}
                ,{field:'id',title:'ID', width:80, sort: true}
                ,{field:'partner_trade_no',title:'{!! trans('app.partner_trade_no')!!}'}
                ,{field:'nickname',title:'{!! trans('user.label.nickname')!!}'}
                ,{field:'phone',title:'{!! trans('user.label.phone')!!}'}
                ,{field:'avatar_url',title:'{!! trans('user.label.avatar_url')!!}',toolbar:'#imageTEM'}
                ,{field:'price',title:'{!! trans('app.price')!!}'}
                ,{field:'status_desc',title:'{!! trans('app.status')!!}'}
                ,{field:'created_at',title:'{!! trans('app.created_at')!!}'}
                ,{field:'score',title:'操作', width:150, align: 'right',toolbar:'#barDemo'}
            ]]
            ,id: 'fb-table'
            ,page: true
            ,limit: '{{ config('app.limit') }}'
            ,height: 'full-200'
        });

    });
</script>
{!! Theme::partial('common_handle_js') !!}