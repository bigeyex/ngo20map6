
$('.check-group label').click(function(e){
    $(e.target).toggleClass('selected');
    var selected_status = $(e.target).hasClass('selected');
    if(selected_status){
        $('#'+$(e.target).attr('id')+'-checkbox').attr('checked', 'checked');
    }
    else{
        $('#'+$(e.target).attr('id')+'-checkbox').removeAttr('checked');
    }
    var p = $(e.target).parent().parent();
    var text = p.find('.selected').map(function(){return $(this).text()}).get().join(', ');
    p.parent().find('input[type="text"]').val(text);

});

$('.multi-select-box').click(function(e){
    var drop_down = $(e.currentTarget).find('.multi-select-dropdown');
    drop_down.toggle();
    e.stopPropagation();
});

$('.multi-select-dropdown').click(function(e){
    e.stopPropagation();
});

$(document.body).click(function(){
    $('.multi-select-dropdown').hide();
});


new PCAS("province","city","county");

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
        map.centerAndZoom(point, 16);
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