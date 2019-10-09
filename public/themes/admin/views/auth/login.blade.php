

<div class="login layui-anim layui-anim-up">
	<div class="login-con">
		<div class="login-con-title">
			<img src="http://images.feibu.info/lpxy/logo.png"/>
			<p>领跑校园管理后台</p>
		</div>
		{!! Theme::partial('message') !!}
		{!!Form::vertical_open()->id('login')->method('POST')->class('layui-form')->action(url('admin/login')) !!}
			
			<input name="email" placeholder="邮箱"  type="text" lay-verify="required" class="layui-input" >
			<input name="password" lay-verify="required" placeholder="密码"  type="password" class="layui-input">
			
			<input value="登录" lay-submit lay-filter="login" style="width:100%;" type="submit" class="login_btn">
			<input id="rememberme" type="hidden" name="rememberme" value="1">
		{!!Form::Close()!!}
	</div>
</div>
