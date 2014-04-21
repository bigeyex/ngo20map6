<?php

$debug_mode = true;

$admin_zone = array();

$pass=true;

/* auto login feature */
if(!isset($_SESSION['login_user']) && isset($_COOKIE['ngo20_login_email'])){
	$user_model = M('Users');
	$login_email = $_COOKIE['ngo20_login_email'];
	$login_key = $_COOKIE['ngo20_login_key'];
	$login_token = $_COOKIE['ngo20_login_token'];
	
	$user = $user_model->where(array('email'=>$_COOKIE['ngo20_login_email']))->find();
	if(!empty($user)){
		$verify_token = md5($login_key . SALT_KEY . $user['password']);
		if($login_token == $verify_token){
			$_SESSION['login_user'] = $user;
		}
	}
}

/* check for admin zone */
if(!$_SESSION['login_user']['is_admin'] && ( in_array(strtolower(MODULE_NAME), $admin_zone) ||
    in_array(strtolower(MODULE_NAME) . '/' . strtolower(ACTION_NAME), $admin_zone) )){
    setflash('error','','您的权限不足');
    $pass=false;
}
if(!$pass){
    $_SESSION['last_page'] = $_SERVER["REQUEST_URI"];
    redirect(__APP__.'/Index/index');
}

/* return attribute of current login user */
function user($attr=null, $value=null){
	if(!isset($_SESSION['login_user'])){
		return false;
	}
	if($attr == 'local_map' && !isset($_SESSION['login_user']['local_map'])){
        $_SESSION['login_user']['local_map'] = T('local_map')->with('admin_id', user('id'))->select();
    }
	if($attr === null){
		return true;
	}
	if($value === null){	// read user info
		if($attr == 'type_label'){
			switch ($_SESSION['login_user']['type']) {
				case 'ngo':
					return '公益组织';
					break;
				case 'ind':
					return '公益人';
					break;
				case 'csr':
					return '企业';
					break;
				case 'fund':
					return '基金会';
					break;
			}
		}

		return $_SESSION['login_user'][$attr];
	}
	else{					//write user info
		$_SESSION['login_user'][$attr] = $value;
	}
}


?>