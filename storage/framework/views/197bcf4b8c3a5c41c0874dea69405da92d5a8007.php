<?php if($message = Session::get('success')): ?>
<div class="alert alert-success alert-dismissable">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <strong><?php echo e(trans('app.flashsuccess')); ?></strong> <?php echo e($message); ?>

</div>
<?php echo e(Session::forget('success')); ?>

<?php endif; ?>

<?php if($message = Session::get('error')): ?>
<div class="alert alert-danger alert-dismissable">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <strong><?php echo e(trans('app.flasherror')); ?>:</strong> <?php echo e($message); ?>

</div>
<?php echo e(Session::forget('error')); ?>

<?php endif; ?>

<?php if($message = Session::get('warning')): ?>
<div class="alert alert-warning alert-dismissable">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <strong><?php echo e(trans('app.flashwarning')); ?>:</strong> <?php echo e($message); ?>

</div>
<?php echo e(Session::forget('warning')); ?>

<?php endif; ?>

<?php if($message = Session::get('info')): ?>
<div class="alert alert-info alert-dismissable">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <strong><?php echo e(trans('app.flashinfo')); ?>:</strong> <?php echo e($message); ?>

</div>
<?php echo e(Session::forget('info')); ?>

<?php endif; ?>
