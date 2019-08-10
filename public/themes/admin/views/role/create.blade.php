<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans("role.name") }}</cite></a><span lay-separator="">/</span>
            <a><cite>添加{{ trans("role.name") }}</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('role')}}" method="POST" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('role.label.name')!!}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" lay-verify="title" autocomplete="off" placeholder="请输入{!! trans('role.label.name')!!}" class="layui-input" >
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('role.label.slug')!!}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="slug" placeholder="请输入{!! trans('role.label.slug')!!}" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">权限</label>
                        <div class="layui-col-md10">
                            @foreach($permissions as $f_key => $father)
                                <div class="top-permission layui-col-md10">
                                    <a href="javascript:;" class="display-sub-permission-toggle">
                                        <i class="layui-icon layui-icon-up"></i>
                                    </a>
                                    <input type="checkbox" name="permissions[]" value="{{ $father->id }}" class="top-permission-checkbox" lay-skin="primary" title="{{ $father->name }}" lay-filter="top-permission-checkbox">
                                </div>
                                <div class="sub-permissions layui-col-md9 layui-col-md-offset1">
                                @if(isset($father->sub))
                                    @foreach($father->sub as $key => $item)
                                        <div class="layui-col-sm3">
                                            <input type="checkbox" name="permissions[]" value="{{ $item->id }}" class="sub-permission-checkbox" lay-skin="primary" title="{{ $item->name }}" lay-filter="sub-permission-checkbox">
                                        </div>
                                    @endforeach
                                @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                        </div>
                    </div>
                    {!!Form::token()!!}
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    layui.use(['jquery','form'], function(){
        var form = layui.form;
        form.render();
        var $ = layui.$;

        $(".display-sub-permission-toggle").click(function(){
            if($(this).parents('.top-permission').next('.sub-permissions').css("display") == 'none')
            {
                $(this).children('i').removeClass('layui-icon-down').addClass('layui-icon-up').parents('.top-permission').next('.sub-permissions').show();
            }else{
                $(this).children('i').removeClass('layui-icon-up').addClass('layui-icon-down').parents('.top-permission').next('.sub-permissions').hide();
            }
        });

        form.on('checkbox(top-permission-checkbox)', function(data){
            if(data.elem.checked){
                $(this).parents('.top-permission').next('.sub-permissions').find('.sub-permission-checkbox').prop('checked', true);
            }else{
                $(this).parents('.top-permission').next('.sub-permissions').find('.sub-permission-checkbox').prop('checked', false);
            }
            form.render();
        });
        form.on('checkbox(sub-permission-checkbox)', function(data){
            if(data.elem.checked){
                $(this).parents('.sub-permissions').prev('.top-permission').find('.top-permission-checkbox').prop('checked', true);
            }else{
                var sub_check = false; //没有子选择
                $(this).parents('.sub-permissions').find('.sub-permission-checkbox').each(function(){
                    if($(this).prop('checked')){
                        sub_check = true;
                        return false;
                    }
                });
                if(!sub_check){
                    $(this).parents('.sub-permissions').prev('.top-permission').find('.top-permission-checkbox').prop('checked', false);
                }
            }
            form.render();
        });

    });
</script>
<script>

</script>