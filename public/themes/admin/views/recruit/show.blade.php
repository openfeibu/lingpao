<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>招聘信息详情</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('recruit/'.$recruit->id)}}" method="post" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">标题</label>
                        <div class="layui-input-inline">
                            <input type="text" name="title" autocomplete="off" placeholder="请输入标题" class="layui-input" value="{{$recruit['title']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">地址</label>
                        <div class="layui-input-inline">
                            <input type="text" name="address" autocomplete="off" placeholder="请输入地址" class="layui-input" value="{{$recruit['address']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">薪水</label>
                        <div class="layui-input-inline">
                            <input type="text" name="salary" autocomplete="off" placeholder="请输入薪水" class="layui-input" value="{{$recruit['salary']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">图片</label>
                        {!! $recruit->files('image')
                        ->url($recruit->getUploadUrl('image'))
                        ->uploader()!!}
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">要求</label>
                        <div class="layui-input-inline">
                            <input type="text" name="requirement" autocomplete="off" placeholder="请输入要求" class="layui-input" value="{{$recruit['requirement']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">职责</label>
                        <div class="layui-input-inline">
                            <input type="text" name="duty" autocomplete="off" placeholder="请输入经度" class="layui-input" value="{{$recruit['duty']}}">
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
{!! Theme::asset()->container('ueditor')->scripts() !!}