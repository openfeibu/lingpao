<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans('task_order.custom_order.name') }}</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('custom_order/'.$custom_order->id)}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.order_sn')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['order_sn'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('task_order.label.user')!!}：</label>
                        <div class="layui-input-block">
                            <p class="input-p">
                                <img src="{{ $custom_order['avatar_url'] }}" class="avatar">
                                <span>{{ $custom_order['nickname'] }} （{{ $custom_order['phone'] }}）</span>
                            </p>
                        </div>
                    </div>
                    @if($custom_order['deliverer_id'])
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('task_order.label.deliverer')!!}：</label>
                            <div class="layui-input-block">
                                <p class="input-p">
                                    <img src="{{ $custom_order['deliverer_avatar_url'] }}" class="avatar">
                                    <span>{{ $custom_order['deliverer_nickname'] }} （{{ $custom_order['deliverer_phone'] }}）</span>
                                </p>
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.best_time')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['best_time'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.postscript')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['postscript'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.order_price')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['order_price'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.tip')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['tip'] }}</p>
                        </div>
                    </div>
                    @if($custom_order['coupon_price'] > 0)
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('app.coupon_price')!!}：</label>
                            <div class="layui-input-inline">
                                <p class="input-p">-{{ $custom_order['coupon_price'] }}（{{ $custom_order['coupon_name']  }}）</p>
                            </div>
                        </div>
                    @endif
                    @if($custom_order['service_price'])
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('app.service_price')!!}：</label>
                            <div class="layui-input-inline">
                                <p class="input-p">{{ $custom_order['service_price'] }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.total_price')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['total_price'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.payment.name')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['payment_desc'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.order_status')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['status_desc'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.created_at')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $custom_order['created_at'] }}</p>
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
