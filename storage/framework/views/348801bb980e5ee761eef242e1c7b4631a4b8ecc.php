<div class="layui-side layui-side-menu">
    <div class="layui-side-scroll">
        <div class="logo">管理后台</div>
        <ul class="layui-nav layui-nav-tree" lay-filter="test">
            
            <?php $permissionPresenter = app('App\Repositories\Presenter\PermissionPresenter'); ?>

            <?php echo $permissionPresenter->menus(); ?>

        </ul>

    </div>
</div>

<!-- 左侧菜单结束 -->

