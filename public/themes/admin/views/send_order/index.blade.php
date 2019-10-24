<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("send_order.name") }}管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="tabel-message layui-form">
                <div class="layui-inline">
                    <label class="layui-form-label">{!! trans('app.order_sn')!!}：</label>
                    <div class="layui-input-inline">
                        <input class="layui-input search_key" name="order_sn" id="demoReload" placeholder="ID/昵称/手机号" autocomplete="off">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">{!! trans('task_order.label.user')!!}：</label>
                    <div class="layui-input-inline">
                        <input class="layui-input search_key" name="search_user" id="demoReload" placeholder="ID/昵称/手机号" autocomplete="off">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">{!! trans('task_order.label.deliverer')!!}：</label>
                    <div class="layui-input-inline">
                        <input class="layui-input search_key" name="search_deliverer" id="demoReload" placeholder="ID/昵称/手机号" autocomplete="off">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">{!! trans('app.order_status')!!}</label>
                    <div class="layui-input-block">
                        <select name="order_status" class="search_key">
                            <option value="">全部</option>
                            @foreach(config('common.task_order.order_status') as $order_status)
                            <option value="{{ $order_status }}">{{ trans('task_order.order_status.'.$order_status) }}</option>
                            @endforeach
                        </select>
                    </div>
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
    var main_url = "{{guard_url('send_order')}}";
    var delete_all_url = "{{guard_url('send_order/destroyAll')}}";
    layui.use(['jquery','element','table'], function(){
        var table = layui.table;
        var form = layui.form;
        var $ = layui.$;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('send_order')}}'
            ,cols: [[
                {checkbox: true, fixed: true}
                ,{field:'id',title:'ID', width:80, sort: true}
                ,{field:'user_detail',title:'{!! trans('task_order.label.user')!!}',toolbar:'#userTEM'}
                ,{field:'deliverer_detail',title:'{!! trans('task_order.label.deliverer')!!}',toolbar:'#delivererTEM'}
                ,{title:'{!! trans('app.urgent')!!}',templet: '<div>@{{#  if(d.urgent){ }} 是 @{{# } else { }} 否 @{{#  } }}</div>'}
                ,{field:'order_price',title:'{!! trans('send_order.label.order_price')!!}'}
                ,{title:'{!! trans('app.coupon_price')!!}',templet: '<div>-@{{d.coupon_price }}@{{d.coupon_name }}</div>'}
                ,{title:'{!! trans('send_order.label.carriage')!!}',templet: '<div>@{{d.carriage_data.carriage }} @{{#  if(d.carriage_data.extra_price != 0){ }} (+@{{ d.carriage_data.extra_price }}) @{{# } }}</div>'}
                ,{field:'all_total_price',title:'{!! trans('app.total_price')!!}'}
                ,{field:'payment_desc',title:'{!! trans('app.payment.name')!!}'}
                ,{field:'status_desc',title:'{!! trans('app.order_status')!!}'}
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