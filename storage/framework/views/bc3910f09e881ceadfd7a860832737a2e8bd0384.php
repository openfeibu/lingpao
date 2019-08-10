<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="screen-orientation" content="portrait">
    <meta name="x5-orientation" content="portrait">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo Theme::getTitle(); ?> :: <?php echo e(trans('app.name')); ?></title>
    <?php echo Theme::asset()->styles(); ?>

    
    <?php echo Theme::asset()->scripts(); ?>

    <?php echo Theme::asset()->container('footer')->scripts(); ?>

</head>
<body>
    <?php echo Theme::partial('header'); ?>

    <?php echo Theme::partial('aside'); ?>

    <?php echo Theme::content(); ?>

    <?php echo Theme::partial('footer'); ?>

</body>
</html>
