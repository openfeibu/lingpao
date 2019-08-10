<input type="hidden" name="{!!$field!!}" id="path_{!!$field!!}" value="@if($files){{$files['path']}}@endif"/>
<div class="layui-input-inline">
    <div class="layui-upload-drag" id="uploadImage_{!!$field!!}">
        <i class="layui-icon layui-icon-upload-drag"></i>
        <p>点击上传，或将文件拖拽到此处</p>
        @if($files)
            <img id="image_{!!$field!!}" src="{!!url("/image/sm".$files['path'])!!}" style="position:absolute;height:120px;width:100%;left:0px;top: 0px;">
        @else
            <img id="image_{!!$field!!}" src="" style="position:absolute;height:120px;width:100%;left:0px;top: 0px;display: none;">
        @endif

    </div>
</div>
<script>
    layui.use(['jquery','element','form','table','upload'], function(){
        var $ = layui.$;
        var form = layui.form;
        var upload = layui.upload;
        upload.render({
            elem: '#uploadImage_{!!$field!!}'
            ,accept:'images'
            ,url: '{!! $url !!}'
            ,data: {
                '_token':$('meta[name="csrf-token"]').attr('content')
            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                layer.load(); //上传loading
                obj.preview(function(index, file, result){
                    $('#image_{!!$field!!}').show().attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res, index, upload){
                console.log(res)
                $("#path_{!!$field!!}").val(res.data.path);
                layer.closeAll('loading'); //关闭loading
                layer.msg(res.msg);

            }
            ,error: function(index, upload){
                layer.closeAll('loading'); //关闭loading
            }
        });
    });
</script>