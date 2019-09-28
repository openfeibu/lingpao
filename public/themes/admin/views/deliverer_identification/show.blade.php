<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>骑手认证</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('deliverer_identification/changeStatus?id='.$deliverer_identification->id)}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('deliverer_identification.label.name')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ $deliverer_identification->name }}</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('deliverer_identification.label.student_id_card_image')!!}：</label>
                        <div class="layui-input-block">
                            <p class="input-p"> <img src="{{ url('image/original'.$deliverer_identification->student_id_card_image) }}"></p>
                        </div>
                    </div>
                    @if($deliverer_identification->status != 'checking')
                        <label class="layui-form-label">{!! trans('deliverer_identification.label.status')!!}：</label>
                        <div class="layui-input-inline">
                            <p class="input-p">{{ config('common.deliverer_identification.status.'.$deliverer_identification->status) }}</p>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{!! trans('deliverer_identification.label.content')!!}：</label>
                            <div class="layui-input-inline">
                                <p class="input-p">{{ $deliverer_identification->content }}</p>
                            </div>
                        </div>
                    @else
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('deliverer_identification.label.status')!!}：</label>
                        <div class="layui-input-inline">
                            <select name="status">
                                @foreach(config('common.deliverer_identification.status') as $key => $status)
                                    @if($key != 'checking')
                                    <option value="{{ $status }}" @if($deliverer_identification->status == $status) selected @endif>{{ trans('deliverer_identification.status.'.$status) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! trans('deliverer_identification.label.content')!!}：</label>
                        <div class="layui-input-inline">
                            <textarea name="content" placeholder="审核不通过理由" class="layui-textarea" value="{{ $deliverer_identification->content }}"></textarea>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn " lay-submit="" lay-filter="demo1">立即提交</button>
                        </div>
                    </div>
                    @endif

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
        @if($deliverer_identification->status != 'checking')
        $("input").attr("disabled",true);
        $("select").attr("disabled",true);
        $("textarea").attr("disabled",true);
        form.render();
        @endif
        //监听提交
        form.on('submit(demo1)', function(data){
            data = JSON.stringify(data.field);
            data = JSON.parse(data);
            data['_token'] = "{!! csrf_token() !!}";
            var load = layer.load();
            $.ajax({
                url : "{{guard_url('deliverer_identification/changeStatus?id='.$deliverer_identification->id)}}",
                data :  data,
                type : 'POST',
                success : function (data) {
                    layer.close(load);
                    layer.msg(data.message);
                    if(data.url)
                    {
                        window.location.href = data.url;
                    }
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
