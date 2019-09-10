<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans('take_order.name') }}</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('take_order/'.$take_order->id)}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('take_order.label.name')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order->name }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('take_order.label.student_id_card_image')!!}：</label>
                        <div class="layui-input-block">
                            <p class="input-p"> <img src="{{ url('image/original'.$take_order->student_id_card_image) }}"></p>
                        </div>
                    </div>

                    {!!Form::token()!!}
                </form>
            </div>

        </div>
    </div>
</div>


<script>

    layui.use(['jquery','element','table'], function() {
        var $ = layui.$;
        var form = layui.form;

        form.render();

    });
</script>
