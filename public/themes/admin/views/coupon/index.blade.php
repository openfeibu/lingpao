<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>优惠券活动管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            {!! Theme::partial('message') !!}
            <div class="tabel-message">
                <div class="layui-inline tabel-btn">
                    <button class="layui-btn layui-btn-warm "><a href="{{ url('/admin/coupon/create') }}">添加优惠券活动</a></button>
                    <button class="layui-btn layui-btn-primary " data-type="del" data-events="del">删除</button>
                </div>
                <!--  <div class="layui-inline">
                   <input class="layui-input" name="id" id="demoReload" placeholder="搜索轮播图" autocomplete="off">
                 </div>
                 <button class="layui-btn" data-type="reload">搜索</button> -->
            </div>

            <table id="fb-table" class="layui-table"  lay-filter="fb-table">

            </table>
        </div>
    </div>
</div>
<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-sm" lay-event="del">删除</a>
</script>
<script type="text/html" id="imageTEM">
    <img src="@{{d.image}}" alt="" height="28">
</script>
<script type="text/html" id="switchTEM">
    <form class="layui-form">

        <input type="checkbox" name="switch" lay-skin="switch" @{{# if(d.is_open){ }} checked @{{#  } }} data-type="coupon_is_open" data-events="coupon_is_open" lay-filter="coupon_is_open" coupon_id="@{{ d.id }}">

    </form>
</script>
<script>
    var main_url = "{{guard_url('coupon')}}";
    var delete_all_url = "{{guard_url('coupon/destroyAll')}}";
    layui.use(['jquery','element','table','form'], function(){
        var $ = layui.$;
        var table = layui.table;
        var form = layui.form;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('coupon')}}'
            ,cols: [[
                {checkbox: true, fixed: true}
                ,{field:'id',title:'ID', width:80, sort: true}
                ,{field:'name',title:'{!! trans('coupon.label.name')!!}',edit:'text'}
                ,{field:'price',title:'{!! trans('coupon.label.price')!!}',edit:'text'}
                ,{field:'min_price',title:'{!! trans('coupon.label.min_price')!!}',edit:'text'}
                ,{field:'end_day',title:'{!! trans('coupon.label.end_day')!!}',edit:'text'}
                ,{field:'is_open',title:'{!! trans('coupon.label.is_open')!!}',templet:'#switchTEM'}
                ,{field:'num',title:'{!! trans('coupon.label.num')!!}'}
                ,{field:'receive_num',title:'{!! trans('coupon.label.receive_num')!!}'}
                ,{field:'stock',title:'{!! trans('coupon.label.stock')!!}'}
                ,{field:'score',title:'操作', width:200, align: 'right',toolbar:'#barDemo'}
            ]]
            ,id: 'fb-table'
            ,height: 'full-200'
        });
        form.on('switch(coupon_is_open)', function (data) {
            var ajax_data = {};
            coupon_id = data.elem.getAttribute('coupon_id');
            var is_open = 0;
            if(data.elem.checked)
            {
                is_open = 1;
            }
            ajax_data['_token'] = "{!! csrf_token() !!}";
            ajax_data['is_open'] = is_open;
            var load = layer.load();
            $.ajax({
                url : main_url+'/'+coupon_id,
                data : ajax_data,
                type : 'PUT',
                success : function (data) {
                    layer.close(load);
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    layer.close(load);
                    layer.msg('服务器出错');
                }
            });
        })
    });
</script>
{!! Theme::partial('common_handle_js') !!}