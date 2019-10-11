<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>优惠券活动</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('coupon/'.$coupon->id)}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('coupon.label.name')!!}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="请输入{!! trans('coupon.label.name')!!}" class="layui-input" value="{{ $coupon['name'] }}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('coupon.label.price')!!}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="price" lay-verify="number" autocomplete="off" placeholder="请输入{!! trans('coupon.label.price')!!}" class="layui-input" value="{{ $coupon['price'] }}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('coupon.label.min_price')!!}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="min_price" lay-verify="number" autocomplete="off" placeholder="请输入{!! trans('coupon.label.min_price')!!}" class="layui-input" value="{{ $coupon['min_price'] }}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('coupon.label.num')!!}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="num" lay-verify="number" autocomplete="off" placeholder="请输入{!! trans('coupon.label.num')!!}" class="layui-input" value="{{ $coupon['num'] }}" disabled>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('coupon.label.end_day')!!}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="end_day" lay-verify="number" autocomplete="off" placeholder="请输入{!! trans('coupon.label.end_day')!!}" class="layui-input" value="{{ $coupon['end_day'] }}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('coupon.label.is_open')!!}</label>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="is_open" lay-skin="switch" checked>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                        </div>
                    </div>
                    {!!Form::token()!!}
                    <input type="hidden" name="_method" value="PUT">
                </form>
            </div>

        </div>
    </div>
</div>


