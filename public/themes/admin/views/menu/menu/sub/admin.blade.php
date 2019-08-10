<dl class="layui-nav-child">
    @foreach ($menus as $menu)
        @if($menu->has_role)
            @if ($menu->hasChildren())

            @else
                <dd><a href="{{trans_url($menu->url)}}">{{$menu->name}}</a></dd>
            @endif
        @endif
    @endforeach
</dl>