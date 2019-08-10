@foreach ($menus as $menu)
    @if($menu->has_role)
    <li class="layui-nav-item {{ $menu->active ? 'layui-nav-itemed' : '' }}">
        @if ($menu->hasChildren())
            <a href="javascript:;"><i class="layui-icon {{ $menu->icon }}"></i>{{$menu->name}}</a>
            @include('menu.admin.sub.admin', array('menus' => $menu->getChildren()))
        @else
            <a href="{{trans_url($menu->url)}}"><i class="layui-icon {{ $menu->icon }}"></i>{{$menu->name}}</a>
        @endif
    </li>
    @endif
@endforeach
