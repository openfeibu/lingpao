<div class="main">
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="layui-card mt20">
                <!-- <div class="layui-card-header">待办事项</div> -->
                <div class="layui-card-body">

                    <div class="fb-carousel fb-backlog " lay-anim="" lay-indicator="inside" lay-arrow="none" >
                        <div carousel-item="">
                            <ul class="layui-row fb-clearfix dataBox layui-col-space5">
                                <li class="layui-col-xs3 ">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3>用户总数</h3>
                                        <p><cite>{{ $user_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs3">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3>代拿总数</h3>
                                        <p><cite>{{ $take_order_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs3">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3>帮帮忙总数</h3>
                                        <p><cite>{{ $custom_order_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs3">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3>用户余额总数</h3>
                                        <p><cite>{{ $balance_sum }}</cite></p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>