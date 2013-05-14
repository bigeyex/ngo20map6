<?php


function js($str){
	return '<script type="text/javascript" src="'.__APP__.'/Public/js/'.$str.'.js"></script>';
}

function css($str){
	return '<link href="'.__APP__.'/Public/css/'.$str.'.css" rel="stylesheet"/>';
}

?>