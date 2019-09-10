<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("take_order.name") }}管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="tabel-message layui-form">
                <div class="layui-inline">
                    <input class="layui-input search_key" name="search_name" id="demoReload" placeholder="昵称/手机号" autocomplete="off">
                </div>
                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table id="fb-table" class="layui-table"  lay-filter="fb-table">

            </table>
        </div>
    </div>
</div>

<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-sm" lay-event="edit">查看</a>
</script>
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
<script type="text/html" id="imageStudentIdTEM">
    <img src="@{{d.student_id_card_image_full}}" alt="" height="28">
</script>

<script>
    var main_url = "{{guard_url('take_order')}}";
    var delete_all_url = "{{guard_url('take_order/destroyAll')}}";
    layui.use(['jquery','element','table'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('take_order')}}'
            ,cols: [[
                {checkbox: true, fixed: true}
                ,{field:'id',title:'ID', width:80, sort: true}
                ,{field:'user_detail',title:'{!! trans('task.label.user')!!}',toolbar:'#userTEM'}
                ,{field:'deliverer_detail',title:'{!! trans('task.label.deliverer')!!}',toolbar:'#delivererTEM'}
                ,{field:'coupon_name',title:'{!! trans('task.label.coupon_name')!!}'}
                ,{field:'coupon_price',title:'{!! trans('task.label.coupon_price')!!}'}
                ,{field:'total_price',title:'{!! trans('task.label.total_price')!!}'}
                ,{title:'{!! trans('take_order.label.service_price')!!}',templet: '<div>@{{d.service_price_data.service_price }}</div>'}
                ,{field:'payment_desc',title:'{!! trans('app.payment.name')!!}'}
                ,{field:'status_desc',title:'{!! trans('task.label.order_status')!!}'}
                ,{field:'created_at',title:'{!! trans('app.created_at')!!}'}
                ,{field:'score',title:'操作', width:100, align: 'right',toolbar:'#barDemo'}
            ]]
            ,id: 'fb-table'
            ,page: true
            ,limit: '{{ config('app.limit') }}'
            ,height: 'full-200'
        });


    });
</script>
{!! Theme::partial('common_handle_js') !!}