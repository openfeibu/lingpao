<div class="main">
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="layui-card mt20">
                <div class="layui-card-header">平台交易数据</div> 
                <div class="layui-card-body">
					<div class="fb-carousel fb-backlog" lay-anim="" lay-indicator="inside" lay-arrow="none" >
                        <div carousel-item="">
                            <ul class="layui-row fb-clearfix dataBox layui-col-space30">
                                <li class="layui-col-xs4">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 今日交易量（元）</h3>
                                        <p><cite>{{ $user_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs4 ">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 总交易量（元）</h3>
                                        <p><cite>{{ $take_order_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs4">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 用户总余额（元）</h3>
                                        <p><cite>{{ $custom_order_count }}</cite></p>
                                    </a>
                                </li>
                      
                            </ul>
                        </div>
                    </div>
                   
                </div>
            </div>
			<div class="layui-card mt20">
                <div class="layui-card-header">平台用户数据</div> 
                <div class="layui-card-body">

                    <div class="fb-carousel fb-backlog" lay-anim="" lay-indicator="inside" lay-arrow="none" >
                        <div carousel-item="">
                            <ul class="layui-row fb-clearfix dataBox layui-col-space30">
                                <li class="layui-col-xs4 ">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3>用户总数</h3>
                                        <p><cite>{{ $user_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs4">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3>今日新增用户数</h3>
                                        <p><cite>{{ $take_order_count }}</cite></p>
                                    </a>
                                </li>
                                 <li class="layui-col-xs4 ">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3>骑手总数</h3>
                                        <p><cite>{{ $user_count }}</cite></p>
                                    </a>
                                </li>
                            </ul>
							
                        </div>
                    </div>
                </div>
            </div>
			 <div class="layui-card mt20">
                <div class="layui-card-header">订单交易数据</div> 
                <div class="layui-card-body">
					<div class="fb-carousel fb-backlog" lay-anim="" lay-indicator="inside" lay-arrow="none" >
                        <div carousel-item="">
                            <ul class="layui-row fb-clearfix dataBox layui-col-space30">
                                <li class="layui-col-xs4">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 代拿任务总数</h3>
                                        <p><cite>{{ $user_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs4 ">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 今日代拿任务总数</h3>
                                        <p><cite>{{ $take_order_count }}</cite></p>
                                    </a>
                                </li>
                                
                            </ul>
							<ul class="layui-row fb-clearfix dataBox layui-col-space30">
                                <li class="layui-col-xs4">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 帮帮忙任务总数</h3>
                                        <p><cite>{{ $user_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs4 ">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 今日帮帮忙任务总数</h3>
                                        <p><cite>{{ $take_order_count }}</cite></p>
                                    </a>
                                </li>
                                
                            </ul>
							<ul class="layui-row fb-clearfix dataBox layui-col-space30">
                                <li class="layui-col-xs4">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 代寄任务总数</h3>
                                        <p><cite>{{ $user_count }}</cite></p>
                                    </a>
                                </li>
                                <li class="layui-col-xs4 ">
                                    <a lay-href="" class="fb-backlog-body">
                                        <h3> 今日代寄任务总数</h3>
                                        <p><cite>{{ $take_order_count }}</cite></p>
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