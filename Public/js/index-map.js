
var map = new BMap.Map("allmap");            // 创建Map实例
var point = new BMap.Point(105.537953, 39.075737);    // 创建点坐标
map.centerAndZoom(point,5);                     // 初始化地图,设置中心点坐标和地图级别。
map.enableScrollWheelZoom();                            //启用滚轮放大缩小


$('.main-nav-filter').hover(function(){
	$(this).find('.filter-box').show();
}, function(){
	$(this).find('.filter-box').fadeOut();
});

$('.main-nav-filter').click(function(){
	$(this).find('.filter-box').show();
});

$('.main-nav-filter').blur(function(){
	$(this).find('.filter-box').fadeOut();
});


$('#region-list li').click(function(){
	var province = $(this).attr('val');
	if(province!=''){
		map.setCenter(province);
		map.setZoom(8);
	}
	else{
		var point = new BMap.Point(105.537953, 39.075737);
		map.centerAndZoom(point,5);
	}
	$('#region-filter-button').text($(this).text());
});

$('#field-list li').click(function(){
	var field = $(this).attr('val');
	mapdata.set_field(field);
	list_control.change_viewport();
	$('#field-filter-button').text($(this).text());
});

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
 this.se = false;
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
			if(this.rec_field != '' && (r.rec_field === null || r.rec_field.indexOf(this.rec_field) == -1))
				continue;
			this._accept(r);
		}
		this.sort();
		this._refresh_hotspots();
	},
	init: function(raw){
		this.raw_data = raw;
		this.filter();
		//refresh total count
		$('#ngo-list-section .total-count').text('一共'+this.ngo_data.length+'个');
		$('#csr-list-section .total-count').text('一共'+this.csr_data.length+'个');
		$('#case-list-section .total-count').text('一共'+this.case_data.length+'个');
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
	          {text:d.name, minZoom: 2, maxZoom: 18, userData: d});
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
	trim: function(str, num, dots){
		if(dots == undefined)dots = '...';
		if(str.length > num){
			return str.substring(0, num-2) + dots;
		}
		return str;
	}
}

var list_control = {	//knockout.js model
	detailed_page_size : 11,
	page_size: 3,
	ngo_in_view : [],
	csr_in_view : [],
	case_in_view : [],
	ngo_list: ko.observableArray(),
	csr_list: ko.observableArray(),
	case_list: ko.observableArray(),
	current_type: '',
	pager: ko.observableArray([1,2,3,4,5,6,7,8]),
	page: 1,
	init: function(){
		var self = this;
		this.change_viewport();
		$('#ngo-list-section h4, #ngo-list-section .list-more-link').click(function(){self.zoom_in_list('ngo')});
		$('#csr-list-section h4, #csr-list-section .list-more-link').click(function(){self.zoom_in_list('csr')});
		$('#case-list-section h4, #case-list-section .list-more-link').click(function(){self.zoom_in_list('case')});
		$('.prev-page').click(function(){map.clearOverlays();self.gotoPage(self.page-1)});
		$('.next-page').click(function(){map.clearOverlays();self.gotoPage(self.page+1)});
		$(document).on('mouseenter', '.map-list li', function(){
			var data = ko.dataFor(this);
			info_window.load(data);
		});
		$(document).on('mouseleave', '.map-list li', function(){
			info_window.hide();
		});
		$(document).on('click', '.map-list li', function(){
			$('.item-highlighter').show();
			$('.item-highlighter').css('top', this.offsetTop+13);
		});
	},
	change_viewport: function(){
		this.filter_by_view();
		map.clearOverlays();
		if(this.current_type == ''){
			this.gotoPage(1, 'ngo');
			this.gotoPage(1, 'csr');
			this.gotoPage(1, 'case');
		}
		else{	
			this.gotoPage(1, this.current_type);
		}
	},
	gotoPage: function(page, record_type){
		var self = this;

		if(record_type == undefined){
			record_type = this.current_type;
		}
		var record_base = this[record_type+'_in_view'];
		var records = this[record_type+'_list'];

		var page_size = this.page_size;
		var count = record_base.length;
		var total_page = Math.floor(count/page_size)+1;

		if(total_page<=1){
			$('.pager').hide();
		}
		else{
			$('.pager').show();
		}
		
		if(page>total_page){
			page = total_page;
		}
		else if(page<1){
			page = 1;
		}
		records.removeAll();
		this.page = page;
		// this loop does 2 things:
		// 1. update knockout observed array
		// 2. add markers to the map
		for(var i=0; i<page_size; i++){
			var record_id = (page-1)*page_size+i;
			if(record_id >= count) break;
			var record = record_base[record_id];
			record.class_id = 'record-' + (i+1);
			record.t_text = util.trim(record.name, 12);
			//put a marker on the map
			var myIcon = new BMap.Icon(app_path+"/Public/img/markers/markers-"+record_type+".png", new BMap.Size(22, 30), {  
				anchor: new BMap.Size(11, 30),  
				imageOffset: new BMap.Size(0, 0 - i * 40)
			});
			var point = new BMap.Point(record.longitude, record.latitude);
			var marker = new BMap.Marker(point, {icon: myIcon}); 
			marker.data = record;
			marker.addEventListener('click', function(){
				//open info window
				info_window.load(this.data);
			});
			record.marker = marker;
			map.addOverlay(marker);  
			records.push(record);
		}
		
		// refresh pager
		var pager_place_left = 8;
		this.pager.removeAll();
		if(page > 3){	// case: ... 2 3 4 5 *6*
			for(var i=page-2; i<page; i++){
				this.pager.push(i);
			}	
			pager_place_left -= 2;
		}
		else{	// case: 1 2 3 *4*
			for(var i=1; i<page; i++){
				this.pager.push(i);
				pager_place_left -= 1;
			}
		}
		for(var i=page; i<total_page && pager_place_left>=1; i++, pager_place_left--){
			this.pager.push(i);
		}
		$('.pager div span').click(function(e){
			var page = e.target.innerText;
			map.clearOverlays();
			self.gotoPage(parseInt(page));
		});
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
	},
	zoom_in_list: function(list_name){
		//slide list sections
		
		$('.list-more').slideUp();
		$('.item-highlighter').hide();
		var other_lists = $('.map-list-section').not('#'+list_name+'-list-section');
		other_lists.find('.map-list').slideUp();
		$('#'+list_name+'-list-section .map-list').slideDown().animate({height:$(window).height()-162}, function(){
			$('.map-list').css('overflow', 'auto');
		});

		//refresh map markers
		mapdata.set_type(list_name);
		this.page_size = this.detailed_page_size;
		this.current_type = list_name;
		this.change_viewport();

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
	}
};

map_control.refresh_tilelayer();

/* do list stuff */
$.get(app_path+'/Map/ajax_hotspots', function(result){
	//save to variable
	mapdata.init(result);

	//init the list - 3 for each
	list_control.init();
	ko.applyBindings(list_control);

	map.addEventListener('dragend', function(){
		list_control.change_viewport();
	});
	map.addEventListener('zoomend', function(){
		list_control.change_viewport();
	});
});

/* SECTION: weibo stuff */
/* weibo variables */
var weibo_data;
var weibo_data_index = 0;
var weibo_data_last_index = 0;
var weibo_timer;
var weibo_marker = null;

/* load weibo content */
$.get(app_path+'/Index/load_weibo', function(result){
	weibo_data = result;
	switch_weibo_marker();
	weibo_timer = window.setInterval(switch_weibo_marker, 10000);
}, 'json');


$('#weibo-box').hover(function(){
	window.clearInterval(weibo_timer);
}, function(){
	weibo_timer = window.setInterval(switch_weibo_marker, 10000);
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
		$('#weibo-user-avatar img').attr('src', weibo.avatar_img);
		$('#weibo-time-past').text(moment(weibo.post_time).fromNow());
		$('.weibo-content').html(util.trim(weibo.content, 50, '<a href="javascript:void(0)" onclick="expand_weibo_text()" class="expand-weibo-text">[展开]</a>'));
		if(weibo.image == ''){
			$('.weibo-img').hide();
		}
		else{
			$('.weibo-img').show();
			$('.weibo-img').attr('src', weibo.image);
		}
		$('#weibo-box').fadeIn();
	});
	weibo_data_last_index = weibo_data_index;
	weibo_data_index++;
}

function expand_weibo_text(){
	var weibo = weibo_data[weibo_data_last_index];
	$('.weibo-content').text(weibo.content);
}

/* SECTION: info-window */

var info_window = {
	overlay : null,
	show: function(longitude, latitude, content){
		var self = this;
		if(self.overlay !== null)map.removeOverlay(self.overlay);
		this.overlay = new HTMLOverlay(longitude, latitude, 186, 163, '<div class="info-window"><div class="info-window-bg"></div><div class="info-window-box">'+content+'<div class="info-window-close-button"></div><div class="info-window-triangle"></div></div></div>');
		map.addOverlay(this.overlay);
		$('.info-window-close-button').click(function(){map.removeOverlay(self.overlay);});
	},
	hide: function(){
		var self = this;
		if(self.overlay !== null)map.removeOverlay(self.overlay);
	},
	load: function(data){
		this.show(data.longitude, data.latitude, '<div id="info-window-title">'+util.trim(data.name, 20)+'</div><div id="info-window-place"><span id="info-window-place-label">位置: </span><span id="info-window-place-text">'+data.province+'</span></div><div id="info-window-detail"><span class="waiting-ball"></span></div>');
		if(data.model == 'events'){
			url_part = 'Event';
		}
		else if(data.model == 'users'){
			url_part = 'User';
		}
		$.get(app_path+'/'+url_part+'/get_detail/id/'+data.id, function(res){
			if(!res)return;
			$('#info-window-detail').text(util.trim(res.description, 112));
		}, 'json');
	}
};

map.addEventListener('hotspotclick', function(e){
	var data = e.spots[0].getUserData();
	info_window.load(data);
});

var l2_panel = {
	init: function(){
		$('#l2-panel').height($(window).height() - 111);
	}
};

// l2_panel.init();
