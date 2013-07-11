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

/* filter GET and POST data before use */
function cleanInput($input) {

	$search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
    );

	$output = preg_replace($search, '', $input);
	return $output;
}

function sanitize($input) {
    if (is_array($input)) {
        foreach($input as $var=>$val) {
            $output[$var] = sanitize($val);
        }
    }
    else {
        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);
        }
        $input  = cleanInput($input);
        $output = mysql_real_escape_string($input);
    }
    return $output;
}



?>