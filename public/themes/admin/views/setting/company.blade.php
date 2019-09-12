<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>公司信息管理</cite></a><span lay-separator="">/</span>
            <a><cite>联系我们</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('setting/updateCompany')}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">公司名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="company_name" lay-verify="companyName" autocomplete="off" placeholder="请输入公司名称" class="layui-input" value="{{$company['company_name']}}">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">电话</label>
                        <div class="layui-input-inline">
                            <input type="text" name="tel" lay-verify="tel" autocomplete="off" placeholder="请输入电话" class="layui-input" value="{{$company['tel']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<script>


    layui.use(['jquery','element','form','table','upload'], function(){
        var form = layui.form;
        var $ = layui.$;
        //监听提交
        form.on('submit(demo1)', function(data){
            data = JSON.stringify(data.field);
            data = JSON.parse(data);
            data['_token'] = "{!! csrf_token() !!}";
            var load = layer.load();
            $.ajax({
                url : "{{guard_url('setting/updateCompany')}}",
                data :  data,
                type : 'POST',
                success : function (data) {
                    layer.close(load);
                    layer.msg('更新成功');
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    layer.close(load);
                    layer.msg('服务器出错');
                }
            });
            return false;
        });

    });
</script>