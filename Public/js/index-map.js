
var map = new BMap.Map("allmap");            // 创建Map实例
var point = new BMap.Point(105.537953, 39.075737);    // 创建点坐标
map.centerAndZoom(point,5);                     // 初始化地图,设置中心点坐标和地图级别。
map.enableScrollWheelZoom();                            //启用滚轮放大缩小


var story_board_default_top = $(window).height() - 50;
var story_board_extended_top = 120;
var story_board_expanded = false;
//$('#story-board').css('height', $(window).height()-story_board_extended_top);
$('#story-zone').css('top', story_board_default_top);

$(window).resize(function(){
	//$('#story-board').css('height', $(window).height()-story_board_extended_top);
	story_board_default_top = $(window).height() - 50;
	$('#story-zone').css('top', story_board_default_top);
});



$('#story-zone').mouseenter(function(){
	if(!story_board_expanded){
		$('#story-zone').clearQueue().animate({
			top: story_board_default_top-5
		});
	}
});

$('#story-zone').mouseleave(function(){
	if(!story_board_expanded){
		$('#story-zone').clearQueue().animate({
			top: story_board_default_top
		});
	}
});

$('#story-zone-handle').click(function(e){
	if(story_board_expanded){
		$('#story-zone').animate({
			top: story_board_default_top
		});
		story_board_expanded = false;
	}
	else{
		$('#story-zone').animate({
			top: story_board_extended_top
		});
		story_board_expanded = true;
	}
	e.stopPropagation();
	
});

function HTMLOverlay(lon, lat, px, py, html){
	this._center = new BMap.Point(lon, lat);
	this._px = px;
	this._py = py;
	this._html = html;
}

HTMLOverlay.prototype = new BMap.Overlay();    

HTMLOverlay.prototype.initialize = function(map){    
 this._map = map;        
 var div = $(this._html)[0];    
 div.style.position = "absolute";        
 map.getPanes().markerPane.appendChild(div);      
 this._div = div;       
 return div;    
}  

HTMLOverlay.prototype.draw = function(){    
// 根据地理坐标转换为像素坐标，并设置给容器    
 var position = this._map.pointToOverlayPixel(this._center);    
 this._div.style.left = position.x - this._px + "px";    
 this._div.style.top = position.y - this._py + "px";    
}  


/* SECTION: information list after selecting a menu item */
var mapdata = {
	//variables
	raw_data : [],
	ngo_data : [],
	csr_data : [],
	case_data : [],
	province : '',
	rec_field : '',
	type : '',

	//methods
	
	filter: function(){
		this.ngo_data = [];
		this.csr_data = [];
		this.case_data = [];
		for(var ri in this.raw_data){
			r = this.raw_data[ri];
			if(this.province != '' && r.province.indexOf(this.province) == -1)
				continue;
			if(this.rec_field != '' && r.rec_field.indexOf(this.rec_field) == -1)
				continue;
			this._accept(r);
		}
		this.sort();
		this._refresh_hotspots();
	},
	init: function(raw){
		this.raw_data = raw;
		this.filter();
	},
	set_province: function(province){
		this.province = province;
		this.filter();
	},
	set_field: function(field){
		this.rec_field = field;
		this.filter();
	},
	set_type: function(type){
		this.type = type;
		map_control.refresh_tilelayer();
	},
	sort: function(){
		this.ngo_data.sort(this._compare_func);
		this.csr_data.sort(this._compare_func);
		this.case_data.sort(this._compare_func);
	},
	_accept: function(r){
		if(r.type == 'ngo'){
			this.ngo_data.push(r);
		}
		else if(r.type == 'csr' || r.type == 'ind'){
			this.csr_data.push(r);
		}
		else if(r.type == 'case'){
			this.case_data.push(r);
		}
	},
	_compare_func : function(a, b){
		//the newer the lower
		var da = new Date(a);
		var db = new Date(b);
		if(da.getTime() < db.getTime()){
			return 1;
		}
		if(da.getTime() > db.getTime()){
			return -1;
		}
		return 0;
	},
	_add_hotspots: function(data_array){
		for(var di in data_array){
			d = data_array[di];
			var hotspot = new BMap.Hotspot(new BMap.Point(d.longitude, d.latitude),
	          {text:d.name, minZoom: 2, maxZoom: 18, userData: {'id':d.id, 'type':d.type, 'model':d.model}});
	     	map.addHotspot(hotspot);
		}
	},
	_refresh_hotspots: function(){
		map_control.refresh_tilelayer();
		map.clearHotspots();
	    if(this.type == 'ngo'){
	    	this._add_hotspots(this.ngo_data);
	    }
	    else if(this.type == 'csr'){
	    	this._add_hotspots(this.csr_data);
	    }
	    else if(this.type == 'case'){
	    	this._add_hotspots(this.case_data);
	    }
	    else{
	    	this._add_hotspots(this.csr_data);
	    	this._add_hotspots(this.ngo_data);
	    	this._add_hotspots(this.case_data);
	    }
	}
};

var util = {
	trim: function(str, num){
		if(str.length > num){
			return str.substring(0, num-3) + '...';
		}
		return str;
	}
}

var list_control = {
	rec_per_page : 10,
	ngo_in_view : [],
	csr_in_view : [],
	case_in_view : [],
	init: function(){
		var self = this;
		this.filter_by_view();
		this.render_list('ngo');
		this.render_list('csr');
		this.render_list('case');
		this.adjust_list_style();
		$('#ngo-list-section h4, #ngo-list-section .list-more-link').click(function(){self.zoom_in_list('ngo')});
		$('#csr-list-section h4, #csr-list-section .list-more-link').click(function(){self.zoom_in_list('csr')});
		$('#case-list-section h4, #case-list-section .list-more-link').click(function(){self.zoom_in_list('case')});

	},
	filter_by_view: function(){
		var bounds = map.getBounds();
		var minlat = bounds.getSouthWest().lat;
		var minlon = bounds.getSouthWest().lng;
		var maxlat = bounds.getNorthEast().lat;
		var maxlon = bounds.getNorthEast().lng;
		this.ngo_in_view = [];
		this.csr_in_view = [];
		this.case_in_view = [];
		for(var di in mapdata.ngo_data){
			var d = mapdata.ngo_data[di];
			if(d.longitude>minlon && d.longitude<maxlon && d.latitude>minlat && d.latitude<maxlat){
				this.ngo_in_view.push(d);
			}
		}
		for(var di in mapdata.csr_data){
			var d = mapdata.csr_data[di];
			if(d.longitude>minlon && d.longitude<maxlon && d.latitude>minlat && d.latitude<maxlat){
				this.csr_in_view.push(d);
			}
		}
		for(var di in mapdata.case_data){
			var d = mapdata.case_data[di];
			if(d.longitude>minlon && d.longitude<maxlon && d.latitude>minlat && d.latitude<maxlat){
				this.case_in_view.push(d);
			}
		}
		this.render_list('ngo');
		// this.render_list('csr');
		// this.render_list('case');
	},
	render_list: function(list_name, page){
		if(page == undefined)page=1;
		var list_data = this[list_name+'_in_view'];
		$('#'+list_name+'-list-section ul').html('');
		var list_length = list_data.length;
		for(var i=this.rec_per_page*(page-1);i<list_length;i++){
			var d = list_data[i];
			$('#'+list_name+'-list-section ul').append('<li data-id="'+d.id+'">'+util.trim(d.name,14)+'</li>');
		}
	},
	render_pager: function(){

	},
	_fill_list: function(arr, container, num){
		var bounds = map.getBounds();
		var minlat = bounds.getSouthWest().lat;
		var minlon = bounds.getSouthWest().lng;
		var maxlat = bounds.getNorthEast().lat;
		var maxlon = bounds.getNorthEast().lng;
		for(var di in arr){
			if(num <= 0)break;
			d = arr[di];
			if(d.longitude>minlon && d.longitude<maxlon && d.latitude>minlat && d.latitude<maxlat){
				$('#'+container+'-list-section ul').append('<li data-id="'+d.id+'">'+util.trim(d.name,14)+'</li>');
				num--;
			}
		}
	},
	adjust_list_style : function(){
		$('#ngo-list-section li').each(function(i){$(this).css('background-position', '0px '+(-40*i+7)+'px')});
		$('#csr-list-section li').each(function(i){$(this).css('background-position', '0px '+(-40*i+7)+'px')});
		$('#case-list-section li').each(function(i){$(this).css('background-position', '0px '+(-40*i+7)+'px')});
	},
	zoom_in_list: function(list_name){
		//slide list sections
		$('.list-more').slideUp();
		$('.item-highlighter').hide();
		var other_lists = $('.map-list-section').not('#'+list_name+'-list-section');
		other_lists.find('ul').slideUp();
		$('#'+list_name+'-list-section ul').slideDown().animate({height:$(window).height()-169}, function(){
			$('#map-list ul').css('overflow', 'auto');
		});

		//refresh map markers
		mapdata.set_type(list_name);

		//design pager
	}
}

var map_control = {
	tileLayer : null,

	refresh_tilelayer: function(){
		if(this.tileLayer != null){
			map.removeTileLayer(this.tileLayer);
		}
		this.tileLayer = new BMap.TileLayer({transparentPng:true});
		this.tileLayer.getTilesUrl = function(tileCoord, zoom) {
			var x = tileCoord.x;
			var y = tileCoord.y;
			return app_path+'/Runtime/Cache/tile-' + zoom + '-' + x + '-' + y + '-'+mapdata.province+'-'+mapdata.rec_field+'-'+mapdata.type+'.gif';
		};
		map.addTileLayer(this.tileLayer);
	},
};

map_control.refresh_tilelayer();

/* do list stuff */
$.get(app_path+'/Map/ajax_hotspots', function(result){
	//save to variable
	mapdata.init(result);

	//init the list - 3 for each
	list_control.init();

	map.addEventListener('dragend', function(){
		list_control.filter_by_view();
	});
	map.addEventListener('zoomend', function(){
		list_control.filter_by_view();
	});
});

/* SECTION: weibo stuff */
/* weibo variables */
var weibo_data;
var weibo_data_index = 0;
var weibo_timer;
var weibo_marker = null;

/* load weibo content */
$(function(){
	$.get(app_path+'/Index/load_weibo', function(result){
		weibo_data = result;
		switch_weibo_marker();
		weibo_timer = window.setInterval(switch_weibo_marker, 10000)
	}, 'json');
});

/* switch weibo marker according to weibo list */
function switch_weibo_marker(){
	if(weibo_marker != null){
		map.removeOverlay(weibo_marker);
	}
	if(weibo_data_index >= weibo_data.length){
		weibo_data_index = 0;
	}
	var weibo = weibo_data[weibo_data_index];
	weibo_marker = new HTMLOverlay(weibo.longitude, weibo.latitude, 21, 46, '<div class="weibo-marker"><div class="weibo-marker-bg"><img width="30" src="'+weibo.avatar_img+'"/></div><div class="weibo-marker-shadow"></div></div>');
	map.addOverlay(weibo_marker);
	$('.weibo-marker-bg').animate({top:0});
	$('.weibo-marker-shadow').animate({top:40});
	$('#weibo-box').fadeOut(function(){
		$('.weibo-user-name').text("@"+weibo.weibo_name);
		$('.weibo-content').text(weibo.content);
		if(weibo.image == ''){
			$('.weibo-img').hide();
		}
		else{
			$('.weibo-img').show();
			$('.weibo-img').attr('src', weibo.image);
		}
		$('#weibo-box').fadeIn();
	});

	weibo_data_index++;
}

/* process hotspot data */
