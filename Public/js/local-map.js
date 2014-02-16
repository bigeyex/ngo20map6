
var map = new BMap.Map("allmap");            // 创建Map实例
var point = new BMap.Point(105.537953, 39.075737);    // 创建点坐标
map.centerAndZoom(point,5);                     // 初始化地图,设置中心点坐标和地图级别。
// map.enableScrollWheelZoom();                            //启用滚轮放大缩小
map.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_BOTTOM_RIGHT, type: BMAP_NAVIGATION_CONTROL_LARGE, offset: new BMap.Size(0, 60)})); 

$('#search-textbox').keypress(function(e){
	if(e.which == 13){
		mapdata.set_key($('#search-textbox').val());
		list_control.change_viewport();
	}
});

$('#search-action-button').click(function(){
	mapdata.set_key($('#search-textbox').val());
	list_control.change_viewport();
});

$('#keywords-filter span').click(function(){
	var text = $(this).text();
	$('#search-textbox').val(text);
	mapdata.set_key(text);
	list_control.change_viewport();
});

$('.remove-keyword-link').click(function(){
	$('#search-textbox').val('');
	mapdata.set_key('');
	list_control.change_viewport();
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
	province : '',
	key: '',
	rec_field : '',
	model : '',
	type : '',
	medal : '',

	
	change_numbers: function(){
		var self = this;
		$.get(app_path+'/Map/get_numbers', {
				type : self.type,
				field : self.rec_field,
				model : self.model,
				key : self.key,
			}, function(data){
				$('.item-count').hide();
				if(this.current_type != ''){
					$('#'+self.type+'-list-section .item-count').show();
					$('#'+self.type+'-list-section .item-count').text('('+data.num+')');
				}
			}
		);
	},
	init: function(){
	},
	set_province: function(province){
		this.province = province;
	},
	set_key: function(key){
		if(key == this.key)return;
		var self = this;
		this.key = key;
		// $.get(app_path+'/Map/ajax_hotspots/key/'+key, function(result){
		// 	self.init(result);
		// 	list_control.change_viewport();
		// });
	},
	set_field: function(field){
		this.rec_field = field;
		map_control.refresh_tilelayer();
		this.change_numbers();
	},
	set_type: function(type){
		this.type = type;
		map_control.refresh_tilelayer();
		this.change_numbers();
	},
	set_model: function(model){
		this.model = model;
		map_control.refresh_tilelayer();
		this.change_numbers();
	},
	set_medal: function(model){
		this.medal = model;
		map_control.refresh_tilelayer();
		this.change_numbers();
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
	detailed_page_size : 10,
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
		$('.map-type-filters span').buttongroup(function(type){
			mapdata.set_model(type);
			list_control.change_viewport();
		});
	},
	change_viewport: function(){
		this.filter_by_view();
		
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
			var myIcon = new BMap.Icon(app_path+"/Public/img/markers/markers-"+record_type+".png", new BMap.Size(18, 25), {  
				anchor: new BMap.Size(10, 25),  
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
		for(var i=page; i<=total_page && pager_place_left>=1; i++, pager_place_left--){
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
		var item_count=0;
		var self = this;
		this.ngo_in_view = [];
		this.csr_in_view = [];
		this.case_in_view = [];
		$.get(app_path+'/Map/get_onscreen_data', {
				type : mapdata.type,
				field : mapdata.rec_field,
				model : mapdata.model,
				medal : mapdata.medal,
				key : mapdata.key,
				start_lon : minlon,
				end_lon : maxlon,
				start_lat : minlat,
				end_lat : maxlat
			}, function(data){
				// store queried data into 3 categories
				map.clearHotspots();
				for(var di in data){
					var d = data[di];
					if(d.model == 'users'){
						d.href = app_path+'/User/view/id/'+d.id;
					}
					else{
						d.href = app_path+'/Event/view/id/'+d.id;
					}
					if(d.type == 'ngo' || d.type == 'fund'){
						self.ngo_in_view.push(d);
					}
					else if(d.type == 'csr' || d.type == 'ind'){
						self.csr_in_view.push(d);
					}
					else if(d.type == 'case'){
						self.case_in_view.push(d);
					}

					//refresh hotsopt
					var hotspot = new BMap.Hotspot(new BMap.Point(d.longitude, d.latitude),
			          { minZoom: 2, maxZoom: 18, userData: d, offsets: [10,10,10,10]});
			     	map.addHotspot(hotspot);
				}

				//reverse the order of 3 lists
				//so can display in temporal order, from newest to oldest.
				self.ngo_in_view.reverse();
				self.csr_in_view.reverse();
				self.case_in_view.reverse();

				//set the content of left list
				map.clearOverlays();
				if(self.current_type == ''){
					self.gotoPage(1, 'ngo');
					self.gotoPage(1, 'csr');
					self.gotoPage(1, 'case');
				}
				else{	
					self.gotoPage(1, this.current_type);
		}

			}

		,'json');

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
		$('#'+list_name+'-type-filter').show();
		$('.map-type-filters').show();
		//refresh map markers
		mapdata.set_type(list_name);
		this.page_size = this.detailed_page_size;
		this.current_type = list_name;
		this.change_viewport();

		$('#'+list_name+'-type-filter span.all').click();
		
		// change the appearance of little triangles
		var type_list = ['ngo', 'csr', 'case'];
		for(var type_id in type_list){
			var type = type_list[type_id];
			if(type == list_name){
				// change the direction of triangle to "down"
				var item = $('.sprite-icons-'+type+'-arrow-right');
				item.addClass('sprite-icons-'+type+'-arrow-down');
				item.removeClass('sprite-icons-'+type+'-arrow-right');
			}
			else{
				// change the direction of triangle to "right"
				var item = $('.sprite-icons-'+type+'-arrow-down');
				item.addClass('sprite-icons-'+type+'-arrow-right');
				item.removeClass('sprite-icons-'+type+'-arrow-down');
			}
		}

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
			return app_path+'/Runtime/Cache/tile-' + zoom + '-' + x + '-' + y + '-'+mapdata.key+'-'+mapdata.rec_field+'-'+mapdata.type+'-'+mapdata.model+'-'+mapdata.medal+'.gif';
		};
		map.addTileLayer(this.tileLayer);
	}
};

map_control.refresh_tilelayer();

/* do list stuff */
$(function(){
	//save to variable
	mapdata.init();

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


function add_curve(org, dst, color, width){
	var pointa = new BMap.Point(org.longitude,org.latitude);
	var pointb = new BMap.Point(dst.longitude,dst.latitude);
	var curve = new BMapLib.CurveLine([pointa, pointb], {strokeColor:color, strokeWeight:width, strokeOpacity:0.4}); //创建弧线对象
	map.addOverlay(curve); //添加到地图中
	return curve;
}

/* SECTION: info-window */

var info_window = {
	overlay : null,
	curves : [],
	_show: function(longitude, latitude, content){
		var self = this;
		this.overlay = new HTMLOverlay(longitude, latitude, 186, 163, '<div class="info-window"><div class="info-window-bg"></div><div class="info-window-box">'+content+'<div class="info-window-close-button"></div><div class="info-window-triangle"></div></div></div>');
		map.addOverlay(this.overlay);
		$('.info-window-close-button').click(function(){map.removeOverlay(self.overlay);});
	},
	hide: function(){
		var self = this;
		if(self.overlay !== null)map.removeOverlay(self.overlay);
		if(self.curves.length > 0){
			for(var i in curves){
				map.removeOverlay(curves[i]);
			}
		}
	},
	load_menu: function(){
		if(saved_hotspots.length == 1){
			this.load(saved_hotspots[0].getUserData());
		}
		else{
			var menu_list = '';
			for(var spoti in saved_hotspots){
				var data = saved_hotspots[spoti].getUserData();
				if(!data)continue;
				if(data.model == 'events'){
					url_part = 'Event';
				}
				else if(data.model == 'users'){
					url_part = 'User';
				}
				var data_url = app_path+'/'+url_part+'/view/id/'+data.id;
				menu_list += '<li class="'+data.type+'"><a target="_blank" href="'+data_url+'">'+util.trim(data.name, 20)+'</a></li>'
			}
			var popup_position = saved_hotspots[0].getPosition();
			this.hide();
			this._show(popup_position.lng, popup_position.lat, '<div id="info-window-title">共有'+saved_hotspots.length+'条信息</div><a class="zoom-in-link" onclick="map.zoomIn();map.panTo(new BMap.Point('+popup_position.lng+','+popup_position.lat+'));">放大区域</a><div id="info-window-detail-list"><ul>'+menu_list+'</ul></div>');
		}
	},
	load: function(data){
		if(data.model == 'events'){
			url_part = 'Event';
		}
		else if(data.model == 'users'){
			url_part = 'User';
		}

		if(data.model == 'users' && data.type == 'ngo'){
			spider_link = '<div class="show-spider-link">展开公益网络</div>';
		}
		else{
			spider_link = '';
		}
		var data_url = app_path+'/'+url_part+'/view/id/'+data.id;
		this.hide();
		this._show(data.longitude, data.latitude, '<div id="info-window-title"><a href="'+data_url+'" target="_blank">'+util.trim(data.name, 20)+'</a></div><div id="info-window-place"><span id="info-window-place-label">位置: </span><span id="info-window-place-text">'+data.province+'</span></div><div id="info-window-detail"><span class="waiting-ball"></span></div>'+spider_link);
		$('.info-window').addClass(data.type);
		
		$.get(app_path+'/'+url_part+'/get_detail/id/'+data.id, function(res){
			if(!res)return;
			$('#info-window-detail').text(util.trim(res.description, 112));
		}, 'json');

		$('.show-spider-link').click(function(){
			$.get(app_path+'/Map/get_network_data/id/'+data.id, function(res){
				for(var ei in res.events){
					add_curve(data,res.events[ei],'#49820b',6);
				}
				for(var ei in res.related_user){
					add_curve(data,res.related_user[ei],'#49820b',2);
				}
				for(var ei in res.related_csr){
					add_curve(data,res.related_csr[ei],'#008ec6',2);
				}
			}, 'json');
		});
	}
};

var hotspot_hover_marker = null;
var saved_hotspots = null;
map.addEventListener('hotspotover', function(e){
	if(hotspot_hover_marker == null){
		var myIcon = new BMap.Icon(app_path+"/Public/img/spinner.gif", new BMap.Size(24, 24), {  
			anchor: new BMap.Size(12, 12)
		});
		var point = e.spots[0].getPosition();
		hotspot_hover_marker = new BMap.Marker(point, {icon: myIcon}); 

		hotspot_hover_marker.addEventListener('click', function(){
			info_window.load_menu();
		});
		map.addOverlay(hotspot_hover_marker);
	}
	else{
		map.addOverlay(hotspot_hover_marker);
		hotspot_hover_marker.setPosition(e.spots[0].getPosition());
	}
	saved_hotspots = e.spots;
	
});
map.addEventListener('hotspotout', function(e){
	map.removeOverlay(hotspot_hover_marker);
	// info_window.load(data);
});


