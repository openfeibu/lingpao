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


<?php if(isset($errors) && $errors->all()): ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="layui-alert layui-bg-red">
            <button type="button" class="layui-close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong><?php echo e($message); ?></strong>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>