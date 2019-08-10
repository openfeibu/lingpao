<?php if(Session::has('status')  && Session::has('message')): ?>
    <?php if(Session::get('status') == 'success'): ?>
        <div class="layui-alert layui-bg-gray">
            <button type="button" class="layui-close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong><?php echo e(Session::get('message')); ?></strong>
        </div>
    <?php elseif(Session::get('status') == 'error'): ?>
        <div class="layui-alert layui-bg-red">
            <button type="button" class="layui-close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong><?php echo e(Session::get('message')); ?></strong>
        </div>
    <?php endif; ?>
<?php endif; ?>