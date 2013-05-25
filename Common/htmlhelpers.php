<?php


function js($str){
	return '<script type="text/javascript" src="'.__APP__.'/Public/js/'.$str.'.js"></script>';
}

function css($str){
	return '<link href="'.__APP__.'/Public/css/'.$str.'.css" rel="stylesheet"/>';
}

function thumb($str, $thumb_level = 0){
	if(is_array($str)){
		if(isset($str['image'])){
			$str = $str['image'];
		}
	}

	if($thumb_level == 0){
		return __APP__.'/Public/Uploaded/'.$str;
	}
	else{
		return __APP__.'/Public/Uploaded/thumb'.$thumb_level.'_'.$str;
	}
}

function place($str, $ifempty = "暂无"){
	if(empty($str)){
		return $ifempty;
	}
	else{
		return $str;
	}
}


?>