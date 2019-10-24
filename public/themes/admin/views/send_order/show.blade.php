<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>{{ trans('send_order.name') }}</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('send_order/'.$send_order->id)}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.order_sn')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['order_sn'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('task_order.label.user')!!}：</label>
                        <div class="layui-input-block">
                            <p class="input-p">
                                <img src="{{ $send_order['avatar_url'] }}" class="avatar">
                                <span>{{ $send_order['nickname'] }} （{{ $send_order['phone'] }}）</span>
                            </p>
                        </div>
                    </div>
                    @if($send_order['deliverer_id'])
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('task_order.label.deliverer')!!}：</label>
                            <div class="layui-input-block">
                                <p class="input-p">
                                    <img src="{{ $send_order['deliverer_avatar_url'] }}" class="avatar">
                                    <span>{{ $send_order['deliverer_nickname'] }} （{{ $send_order['deliverer_phone'] }}）</span>
                                </p>
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item" id="instalment">
                        <fieldset class="layui-elem-field" >
                            <legend>快递</legend>
                            <div class="layui-form-item">
                                <label class="layui-form-label">物品类型：</label>
                                <div class="layui-input-inline">
                                    <p class="input-p">{{ $send_order['item_type_name'] }}</p>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">快递公司：</label>
                                <div class="layui-input-inline">
                                    <p class="input-p">{{ $send_order['express_company_name'] }}</p>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">寄件人信息：</label>
                                <div class="layui-input-inline">
                                    <p class="input-p">{{ $send_order['sender'] }} {{ $send_order['sender_mobile'] }} {{ $send_order['sender_address'] }}</p>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">收件人信息：</label>
                                <div class="layui-input-inline">
                                    <p class="input-p">{{ $send_order['consignee'] }} {{ $send_order['consignee_mobile'] }} {{ $send_order['consignee_address'] }}</p>
                                </div>
                            </div>

                        </fieldset>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.best_time')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['best_time'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.postscript')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['postscript'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.urgent')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">@if($send_order['urgent']) 是 @else 否 @endif</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('send_order.label.order_price')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['order_price'] }}</p>
                        </div>
                    </div>
                    @if($send_order['coupon_price'] > 0)
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('app.coupon_price')!!}：</label>
                            <div class="layui-input-inline">
                                <p class="input-p">-{{ $send_order['coupon_price'] }}（{{ $send_order['coupon_name']  }}）</p>
                            </div>
                        </div>
                    @endif
                    @if($send_order['carriage_data']['carriage'])
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('send_order.label.carriage')!!}：</label>
                            <div class="layui-input-inline">
                                <p class="input-p">{{ $send_order['carriage_data']['carriage'] }} @if($send_order['carriage_data']['extra_price']) + {{ $send_order['carriage_data']['extra_price'] }} @endif ({{ $send_order['carriage_data']['carriage_pay_status_desc'] }})</p>
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.total_price')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['all_total_price'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.payment.name')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['payment_desc'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.order_status')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['status_desc'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.created_at')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $send_order['created_at'] }}</p>
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
