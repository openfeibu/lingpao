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
                        <label class="layui-form-label">{!! trans('app.order_sn')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['order_sn'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('task_order.label.user')!!}：</label>
                        <div class="layui-input-block">
                            <p class="input-p">
                                <img src="{{ $take_order['avatar_url'] }}" class="avatar">
                                <span>{{ $take_order['nickname'] }} （{{ $take_order['phone'] }}）</span>
                            </p>
                        </div>
                    </div>
                    @if($take_order['deliverer_id'])
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('task_order.label.deliverer')!!}：</label>
                            <div class="layui-input-block">
                                <p class="input-p">
                                    <img src="{{ $take_order['deliverer_avatar_url'] }}" class="avatar">
                                    <span>{{ $take_order['deliverer_nickname'] }} （{{ $take_order['deliverer_phone'] }}）</span>
                                </p>
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item" id="instalment">
                        @foreach($take_order['expresses'] as $key => $express)
                            <fieldset class="layui-elem-field" >
                                <legend>快递{{ $key+1 }}</legend>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">取件地点：</label>
                                    <div class="layui-input-inline">
                                        <p class="input-p">{{ $express['take_place'] }}</p>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">收货人信息：</label>
                                    <div class="layui-input-inline">
                                        <p class="input-p">{{ $express['consignee'] }} {{ $express['mobile'] }} {{ $express['address'] }}</p>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">快递公司：</label>
                                    <div class="layui-input-inline">
                                        <p class="input-p">{{ $express['express_company'] }}</p>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">取件码：</label>
                                    <div class="layui-input-inline">
                                        <p class="input-p">{{ $express['take_code'] }}</p>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">快递到校时间：</label>
                                    <div class="layui-input-inline">
                                        <p class="input-p">{{ $express['express_arrive_date'] }}</p>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">快递短信：</label>
                                    <div class="layui-input-inline">
                                        <p class="input-p">{{ $express['description'] }}</p>
                                    </div>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.postscript')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['postscript'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.urgent')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">@if($take_order['urgent']) 是 @else 否 @endif</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.express_count')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['express_count'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.express_price')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['express_price'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.tip')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['tip'] }}</p>
                        </div>
                    </div>
                    @if($take_order['coupon_price'] > 0)
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('app.coupon_price')!!}：</label>
                            <div class="layui-input-inline">
                                <p class="input-p">-{{ $take_order['coupon_price'] }}（{{ $take_order['coupon_name']  }}）</p>
                            </div>
                        </div>
                    @endif
                    @if($take_order['service_price'])
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('app.service_price')!!}：</label>
                            <div class="layui-input-inline">
                                <p class="input-p">{{ $take_order['service_price'] }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.total_price')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['all_total_price'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.payment.name')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['payment_desc'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.order_status')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['status_desc'] }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('app.created_at')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $take_order['created_at'] }}</p>
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
