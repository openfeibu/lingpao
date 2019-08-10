

<div class="login layui-anim layui-anim-up">
    <div class="message">x-admin2.0-管理登录</div>
    <div id="darkbannerwrap"></div>
    @include('notifications')
    {!!Form::vertical_open()->id('login')->method('POST')->class('layui-form')->action(url('admin/login')) !!}
        <input name="email" placeholder="邮箱"  type="text" lay-verify="required" class="layui-input" >
        <hr class="hr15">
        <input name="password" lay-verify="required" placeholder="密码"  type="password" class="layui-input">
        <hr class="hr15">
        <input value="登录" lay-submit lay-filter="login" style="width:100%;" type="submit">
        <hr class="hr20" >
        <input id="rememberme" type="hidden" name="rememberme" value="1">
    {!!Form::Close()!!}
</div>

<script>

</script>
