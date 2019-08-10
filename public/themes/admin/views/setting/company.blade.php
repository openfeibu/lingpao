<div class="main">
    <div class="layui-card fb-minNav">
        <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
            <a href="{{ route('home') }}">主页</a><span lay-separator="">/</span>
            <a><cite>公司信息管理</cite></a><span lay-separator="">/</span>
            <a><cite>联系我们</cite></a>
        </div>
    </div>
    <div class="main_full">
        <div class="layui-col-md12">
            <div class="fb-main-table">
                <form class="layui-form" action="{{guard_url('setting/updateCompany')}}" method="post" lay-filter="fb-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">公司名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="company_name" lay-verify="companyName" autocomplete="off" placeholder="请输入公司名称" class="layui-input" value="{{$company['company_name']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">地址</label>
                        <div class="layui-input-inline">
                            <input type="text" name="address" lay-verify="address" autocomplete="off" placeholder="请输入地址" class="layui-input" value="{{$company['address']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">电话</label>
                        <div class="layui-input-inline">
                            <input type="text" name="tel" lay-verify="tel" autocomplete="off" placeholder="请输入电话" class="layui-input" value="{{$company['tel']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">邮箱</label>
                        <div class="layui-input-inline">
                            <input type="text" name="email" lay-verify="email" autocomplete="off" placeholder="请输入邮箱" class="layui-input" value="{{$company['email']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">QQ</label>
                        <div class="layui-input-inline">
                            <input type="text" name="qq" lay-verify="qq" autocomplete="off" placeholder="请输入QQ" class="layui-input" value="{{$company['qq']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">经纬度</label>
                        <div class="layui-input-inline">
                            <input type="text" name="longitude" lay-verify="longitude" autocomplete="off" placeholder="请输入经度" class="layui-input" value="{{$company['longitude']}}">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="latitude" lay-verify="latitude" autocomplete="off" placeholder="请输入纬度" class="layui-input" value="{{$company['latitude']}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">获取点位</label>
                        <div class="layui-input-inline">
                            <input id="keyword" type="textbox"  class="layui-input"  value="">
                            <input type="button" value="搜索" class="layui-button-mapsearch"  onclick="searchKeyword()">
                            <div class="layui-form-mid layui-word-aux">点击地图快速获取经纬度</div>
                        </div>

                        <div id="map"></div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp"></script>
<script>
    window.onload = function(){
        init();
    }
</script>
<script>
    var searchService,map,markers = [];
    var init = function() {
        var center = new qq.maps.LatLng(23.15641,113.3318);
        map = new qq.maps.Map(document.getElementById('map'),{
            center: center,
            zoom: 13
        });
        //设置圆形
        // new qq.maps.Circle({
        //     center:new qq.maps.LatLng(39.936273,116.44004334),
        //     radius: 2000,
        //     map: map
        // });
        var latlngBounds = new qq.maps.LatLngBounds();
        //调用Poi检索类
        searchService = new qq.maps.SearchService({
            //设置搜索范围为北京
            location: "广州",
            //设置搜索页码为1
            pageIndex: 1,
            //设置每页的结果数为5
            pageCapacity: 5,
            //设置展现查询结构到infoDIV上
            panel: document.getElementById('infoDiv'),
            //设置动扩大检索区域。默认值true，会自动检索指定城市以外区域。
            autoExtend: true,
            //检索成功的回调函数
            complete : function(results){
                var pois = results.detail.pois;
                console.log(results)
                for(var i = 0,l = pois.length;i < l; i++){
                    var poi = pois[i];
                    latlngBounds.extend(poi.latLng);
                    var marker = new qq.maps.Marker({
                        map:map,
                        position: poi.latLng
                    });

                    marker.setTitle(i+1);
                    qq.maps.event.addListener(marker,'click',function(event) {
                        document.getElementsByName('longitude')[0].value = event.latLng.getLng();
                        document.getElementsByName('latitude')[0].value = event.latLng.getLat();
                    })
                    markers.push(marker);
                }
                map.fitBounds(latlngBounds);
            }
        });
        qq.maps.event.addListener(map,'click',function(event) {
            document.getElementsByName('longitude')[0].value = event.latLng.getLng();
            document.getElementsByName('latitude')[0].value = event.latLng.getLat();
        })
    }
    //清除地图上的marker
    function clearOverlays(overlays) {
        var overlay;
        while (overlay = overlays.pop()) {
            overlay.setMap(null);
        }
    }
    //调用poi类信接口
    function searchKeyword() {
        var keyword = document.getElementById("keyword").value;
        region = new qq.maps.LatLng(39.936273,116.44004334);
        clearOverlays(markers);
        // searchService.setPageCapacity(5);
        searchService.search(keyword);//根据中心点坐标、半径和关键字进行周边检索。
    }
</script>
<script>


    layui.use(['jquery','element','form','table','upload'], function(){
        var form = layui.form;
        var $ = layui.$;
        //监听提交
        form.on('submit(demo1)', function(data){
            data = JSON.stringify(data.field);
            data = JSON.parse(data);
            data['_token'] = "{!! csrf_token() !!}";
            var load = layer.load();
            $.ajax({
                url : "{{guard_url('setting/updateCompany')}}",
                data :  data,
                type : 'POST',
                success : function (data) {
                    layer.close(load);
                    layer.msg('更新成功');
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    layer.close(load);
                    layer.msg('服务器出错');
                }
            });
            return false;
        });

    });
</script>