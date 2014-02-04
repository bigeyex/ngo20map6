<?php

function ngo20_md5($str){
	return md5($str);
}

function admin_only(){
	if(!user('is_admin')){
		die('需要管理员权限才能访问此页面');
	}
}

function need_login(){
	if(!user()){
		die('需要登录才能访问此页面');
	}
}

function diehard($var){
	print_r($var);die();
}

function check_model(){
	return true;
}

//read config from db
function CC($key, $val=null){
	$model = new Model();
	$sql = "select from settings where k=$key";
	$result = $model->query($sql);
	if($val === null){	// get a existing setting
		if(empty($result)){
			return null;
		}
		return $result[0]['v'];
	}
	else{	// create a setting or modify a setting
		$val = x($val);
		if(empty($result)){	// create a setting
			$sql = "insert into settings (k,v) values ('$key','$val')";
			$model->query($sql);
		}
		else{
			$sql = "update setting set v='$val' where k='$key'";
			$model->query($sql);
		}
	}

}

function check_profanity_words(){
    foreach(C('profanity_words') as $p_word){
        if(strpos($words, $p_word))
                return false;
    }
    return true;
}

function x($var){
	if(is_array($var)){
		foreach($var as $key=>$value) {
	      if(is_array($value)) { x($value); }
	      else { $var[$key] = mysql_real_escape_string($value); }
	   }
	   return $var;
	}
	else{
		return mysql_real_escape_string($var);
	}
}

function stop_injection(){
	$_GET = x($_GET);
	$_POST = x($_POST);
}

function auto_charset($fContents, $from='gbk', $to='utf-8')
{
	$from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
	$to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
	if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)))
	{
//如果编码相同或者非字符串标量则不转换
		return $fContents;
	}
	if (is_string($fContents))
	{
		if (function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($fContents, $to, $from);
		}
		elseif (function_exists('iconv')) {
			return iconv($from, $to, $fContents);
		}
		else {
			return $fContents;
		}
	}
	elseif (is_array($fContents)) {
		foreach ($fContents as $key => $val)
		{
			$_key = auto_charset($key, $from, $to);
			$fContents[$_key] = auto_charset($val, $from, $to);
			if ($key != $_key) unset($fContents[$key]);
		}
		return $fContents;
	}
	else {
		return $fContents;
	}
}
?>
