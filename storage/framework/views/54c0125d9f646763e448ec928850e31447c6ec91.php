

<div class="login layui-anim layui-anim-up">
	<div class="login-con">
		<div class="login-con-title">飞步科技管理后台</div>
		<?php echo $__env->make('notifications', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
		<?php echo Form::vertical_open()->id('login')->method('POST')->class('layui-form')->action(url('admin/login')); ?>

			<div class="form-title">
				<div class="form-title-item">账号密码登陆</div>
			</div>
			<input name="email" placeholder="邮箱"  type="text" lay-verify="required" class="layui-input" >
			<input name="password" lay-verify="required" placeholder="密码"  type="password" class="layui-input">
			
			<input value="登录" lay-submit lay-filter="login" style="width:100%;" type="submit" class="login_btn">
			<input id="rememberme" type="hidden" name="rememberme" value="1">
		<?php echo Form::Close(); ?>

	</div>
</div>
