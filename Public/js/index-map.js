
var map = new BMap.Map("allmap");            // 创建Map实例
var point = new BMap.Point(105.537953, 39.075737);    // 创建点坐标
map.centerAndZoom(point,5);                     // 初始化地图,设置中心点坐标和地图级别。
// map.enableScrollWheelZoom();                            //启用滚轮放大缩小
map.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_BOTTOM_RIGHT, type: BMAP_NAVIGATION_CONTROL_LARGE, offset: new BMap.Size(0, 60)})); 

$.fn.buttongroup = function(callback){
	var these = this;
	return this.click(function(){
		these.removeClass('checked');
		$(this).addClass('checked');
		callback($(this).attr('value'));
	});
}



$('.main-nav-filter').hover(function(){
	$(this).find('.filter-box').stop().show().css({opacity:1});;
}, function(){
	$(this).find('.filter-box').fadeOut();
});

$('.main-nav-filter').click(function(){
	$(this).find('.filter-box').stop().show().css({opacity:1});;
});


$('#search-box').hover(function(){
	$('#keywords-filter').stop().show().css({opacity:1});;
}, function(){
	$('#keywords-filter').fadeOut();
});

$('#search-textbox').click(function(){
	$('#keywords-filter').stop().show().css({opacity:1});;
});

$('#search-textbox').blur(function(){
	$('#keywords-filter').fadeOut();
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
	$('#region-list li').removeClass('selected');
	$(this).addClass('selected');
});

$('#field-list li').click(function(){
	var field = $(this).attr('val');
	mapdata.set_field(field);
	list_control.change_viewport();
	$('#field-filter-button').text($(this).text());
	$('#field-list li').removeClass('selected');
	$(this).addClass('selected');
});

$('#medal-list li').click(function(){
	var medal = $(this).attr('val');
	mapdata.set_medal(medal);
	list_control.change_viewport();
	list_control.zoom_in_list('ngo');
	if(medal != ''){
		$('#medal-filter-button').text('有勋章');
	}
	else{
		$('#medal-filter-button').text('勋章');
	}
	$('#medal-list li').removeClass('selected');
	$(this).addClass('selected');
	
});

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
		$.get(app_path+'/Map/ajax_hotspots/key/'+key, function(result){
			self.init(result);
			list_control.change_viewport();
		});
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
		$(document).on('mouseleave', '.map-list li', function(){
			info_window.hide();
		});
		$(document).on('click', '.map-list li', function(){
			var data = ko.dataFor(this);
			$('.map-list li').removeClass('selected');
			$(this).addClass('selected');
			$('.item-highlighter').show();
			$('.item-highlighter').css('top', this.offsetTop+8);
			l2_panel.open(data);
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
				for(var di in data){
					var d = data[di];
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
			          {text:d.name, minZoom: 2, maxZoom: 18, userData: d});
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

var WeiboUtil = {
    // 62进制字典
    str62keys: [
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"
    ],
};


/**
 * 62进制值转换为10进制
 * @param {String} str62 62进制值
 * @return {String} 10进制值
 */
WeiboUtil.str62to10 = function(str62) {
	var i10 = 0;
	for (var i = 0; i < str62.length; i++)
	{
		var n = str62.length - i - 1;
		var s = str62[i];
		i10 += this.str62keys.indexOf(s) * Math.pow(62, n);
	}
	return i10;
};
 
/**
 * 10进制值转换为62进制
 * @param {String} int10 10进制值
 * @return {String} 62进制值
 */
WeiboUtil.int10to62 = function(int10) {
	var s62 = '';
	var r = 0;
	while (int10 != 0 && s62.length < 100) {
		r = int10 % 62;
		s62 = this.str62keys[r] + s62;
		int10 = Math.floor(int10 / 62);
	}
	return s62;
};
 
/**
 * mid转换为URL字符
 * @param {String} mid 微博mid，如 "201110410216293360"
 * @return {String} 微博URL字符，如 "wr4mOFqpbO"
 */
WeiboUtil.mid2url = function(mid) {
    if(!mid) {
        return mid;
    }
    mid = String(mid); //mid数值较大，必须为字符串！
	if(!/^\d+$/.test(mid)){ return mid; }
	var url = '';
	
	for (var i = mid.length - 7; i > -7; i = i - 7)	//从最后往前以7字节为一组读取mid
	{
		var offset1 = i < 0 ? 0 : i;
		var offset2 = i + 7;
		var num = mid.substring(offset1, offset2);
		
		num = this.int10to62(num);
		url = num + url;
	}
	
	return url;
};

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
		var weibo_content = util.trim(weibo.content, 40, '<a href="javascript:void(0)" onclick="expand_weibo_text()" class="expand-weibo-text">[展开]</a>');
		if(weibo.image == ''){
			$('.weibo-img').hide();
		}
		else{
			// $('.weibo-img').show();
			// $('.weibo-img').attr('src', weibo.image);
			weibo_content = '<img class="weibo-img" src="'+weibo.image+'" width="50"/>' + weibo_content;
		}
		$('.weibo-content').html(weibo_content);
		$('#weibo-box').fadeIn();
	});
	weibo_data_last_index = weibo_data_index;
	weibo_data_index++;
}

function expand_weibo_text(){
	var weibo = weibo_data[weibo_data_last_index];
	var weibo_content;
	if(weibo.image == ''){
		weibo_content = weibo.content;
	}
	else{
		weibo_content = weibo.content + '<img class="weibo-img-big" src="'+weibo.image+'" width="200"/>';
	}
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
		if(data.model == 'events'){
			url_part = 'Event';
		}
		else if(data.model == 'users'){
			url_part = 'User';
		}
		var data_url = app_path+'/'+url_part+'/view/id/'+data.id;
		this.show(data.longitude, data.latitude, '<div id="info-window-title"><a href="'+data_url+'" target="_blank">'+util.trim(data.name, 20)+'</a></div><div id="info-window-place"><span id="info-window-place-label">位置: </span><span id="info-window-place-text">'+data.province+'</span></div><div id="info-window-detail"><span class="waiting-ball"></span></div>');
		$('.info-window').addClass(data.type);
		
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
		var self = this;
		$('#l2-close-button').click(function(){
			self.close();
		});
	},
	open: function(data){
		$('#l2-panel').height($(window).height() - 98);
		$('#l2-content').html('<div class="waiting-ball"></div>');
		if(data.model == 'users'){
			$('#l2-content').load(app_path+'/User/preview/id/'+data.id, this.calc_height);
		}
		else if(data.model == 'events'){
			$('#l2-content').load(app_path+'/Event/preview/id/'+data.id, this.calc_height);
		}
		$('#l2-panel').show().removeClass().addClass('animated fadeInRight');
		$('#l2-panel').addClass(data.type);
	},
	calc_height: function(){
		$('.preview-roller').height($('#l2-panel').height()-38-$('.preview-header').height()-20);
	},
	close: function(){
		$('#l2-panel').hide();
		$('.map-list li').removeClass('selected');
	}

};

l2_panel.init();

var story_board = {
  story_board_default_top : 0,
  story_board_extended_top : 0,
  story_board_expanded : 0,
  init : function(){
    var self = this;
    self.story_board_default_top = $(window).height() - 50;
    self.story_board_extended_top = 120;
    self.story_board_expanded = false;
    //$('#story-board').css('height', $(window).height()-self.story_board_extended_top);
    $('#story-zone').css('top', self.story_board_default_top);
    $('#story-board').height($(window).height() - 154);

    $(window).resize(function(){
      //$('#story-board').css('height', $(window).height()-self.story_board_extended_top);
      self.story_board_default_top = $(window).height() - 50;
      $('#story-zone').css('top', self.story_board_default_top);
      $('#story-board').height($(window).height() - 154);
    });


    $('#story-zone').mouseenter(function(){
      if(!self.story_board_expanded){
        $('#story-zone').clearQueue().animate({
          top: self.story_board_default_top-5
        });
      }
    });

    $('#story-zone').mouseleave(function(){
      if(!self.story_board_expanded){
        $('#story-zone').clearQueue().animate({
          top: self.story_board_default_top
        });
      }
    });

    $('#story-zone-handle').click(function(e){
      if(self.story_board_expanded){
        $('#story-zone').animate({
          top: self.story_board_default_top
        });
        self.story_board_expanded = false;
      }
      else{
        $('#story-zone').animate({
          top: self.story_board_extended_top
        });
        self.story_board_expanded = true;
      }
      e.stopPropagation();
      
    });
  }
};

story_board.init();


$(function(){
	$('#about-us-link, #beian-link').click(function(e){
		e.stopPropagation();
	});
});
