<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>帮帮忙分类管理</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            {!! Theme::partial('message') !!}
            <div class="tabel-message">
                <div class="layui-inline tabel-btn">
                    <button class="layui-btn layui-btn-warm " data-type="add_custom_order_category" data-events="add_custom_order_category">添加分类</button>
                    {{--<button class="layui-btn layui-btn-primary " data-type="del" data-events="del">删除</button>--}}
                </div>
            </div>

            <table id="fb-table" class="layui-table"  lay-filter="fb-table">

            </table>
        </div>
    </div>
</div>
<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-danger layui-btn-sm" lay-event="del">删除</a>
</script>
<script type="text/html" id="imageTEM">
    <img src="@{{d.image}}" alt="" height="28">
</script>
<script type="text/html" id="switchTEM">
    <form class="layui-form">
        <input type="checkbox" name="switch" lay-skin="switch" @{{# if(d.is_open){ }} checked @{{#  } }} data-type="custom_order_category_is_open" data-events="custom_order_category_is_open" lay-filter="custom_order_category_is_open" category_id="@{{ d.id }}">
    </form>
</script>
<script>
    var main_url = "{{guard_url('custom_order_category')}}";
    var delete_all_url = "{{guard_url('custom_order_category/destroyAll')}}";
    layui.use(['jquery','element','table'], function(){
        var $ = layui.$;
        var table = layui.table;
        var form = layui.form;
        table.render({
            elem: '#fb-table'
            ,url: '{{guard_url('custom_order_category')}}'
            ,cols: [[
                //{checkbox: true, fixed: true}
                {field:'id',title:'ID', width:80}
                ,{field:'name',title:'名称'}
                ,{field:'order',title:'排序', edit:'text'}
                ,{field:'is_open',title:'{!! trans('coupon.label.is_open')!!}',templet:'#switchTEM'}
                ,{field:'score',title:'操作', width:200, align: 'right',toolbar:'#barDemo'}
            ]]
            ,id: 'fb-table'
            ,height: 'full-200'
        });
        form.on('switch(custom_order_category_is_open)', function (data) {
            var ajax_data = {};
            category_id = data.elem.getAttribute('category_id');
            var is_open = 0;
            if(data.elem.checked)
            {
                is_open = 1;
            }
            ajax_data['_token'] = "{!! csrf_token() !!}";
            ajax_data['is_open'] = is_open;
            var load = layer.load();
            $.ajax({
                url : main_url+'/'+category_id,
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