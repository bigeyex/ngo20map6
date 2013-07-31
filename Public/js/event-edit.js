
if(typeof default_province === 'undefined') default_province='';
if(typeof default_city === 'undefined') default_city='';
if(typeof default_county === 'undefined') default_county='';
new PCAS("province","city","county",default_province,default_city,default_county);

var map = new BMap.Map("map-locate-container");
var point = new BMap.Point(116.404, 39.915);
var gc = new BMap.Geocoder();
map.centerAndZoom(point, 5);
map.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_BOTTOM_RIGHT, type: BMAP_NAVIGATION_CONTROL_ZOOM}));  
$("#newform").validationEngine('attach');

function relocate_map(){
    var place = '';
    if($('#province').val() != '')place+=$('#province').val();
    if($('#city').val() != '')place+=$('#city').val();
    if($('#county').val() != '')place+=$('#county').val();
    place+=$('#place').val();

    gc.getPoint(place, function(point){
      if (point) {
        map.centerAndZoom(point, 11);
        addPointMarker(point);
      }
    });
}

function addPointMarker(p){
    map.clearOverlays();
    var marker = new BMap.Marker(p);
    map.addOverlay(marker);
    $('#latitude').val(p.lat);
    $('#longitude').val(p.lng);
}

map.addEventListener("click",function(e){
    addPointMarker(e.point);
});

$('#province, #city, #county, #place').change(relocate_map);


pikaday_i18n = {
    months        : ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','二月'],
    weekdays      : ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
    weekdaysShort : ['日','一','二','三','四','五','六']
};
if($('#begin_time').length > 0){
    var begin_picker = new Pikaday({
        field: document.getElementById('begin_time'),
        format: 'YYYY-MM-DD',
        i18n: pikaday_i18n
    });
    var end_picker = new Pikaday({
        field: document.getElementById('end_time'),
        format: 'YYYY-MM-DD',
        i18n: pikaday_i18n
    });
}