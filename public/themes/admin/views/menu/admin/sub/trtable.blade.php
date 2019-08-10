@foreach ($menus as $menu)
    <tr>
        <td data-field="id"><div class="layui-table-cell laytable-cell-1-id">{!!$menu->id!!}</div></td>
        <td style="padding-left: {!! 30*$level !!}px;">|—{!! str_repeat('—',$level) !!}{!!$menu->name!!}</td>
        <td>{{ $menu->url }}</td>
        <td>{{ $menu->status }}</td>
        <td data-field="score" align="right" data-off="true">
            <div class="layui-table-cell laytable-cell-1-score">
                <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-danger layui-btn-sm" lay-event="del">删除</a>
            </div>
        </td>
    </tr>
    @if ($children = $menu->getChildren())
        @if(count($children))
            @include('menu.admin.sub.trtable', array('menus' => $children,'level' => $level++))
        @endif
    @endifs
@endforeach