<?php
	require('db.php');
	$access_token = '2.00vDZNLCN7hJiB566636c706wty4rD';
	
	$users = query("select * from users where type='ngo' and is_checked=1 and weibo != '' and weibo_provider='新浪微博'");
	foreach($users as $user){
		$weibo = $user['weibo'];
		
		$res = preg_match('/weibo.com\/u\/(.+)/', $weibo, $mat);
		if(isset($mat[1])){	//if hit
			$uid = $mat[1];
			$json = file_get_contents("https://api.weibo.com/2/users/show.json?access_token=$access_token&uid=$uid");
			$arr = json_decode($json, true);
			if(isset($arr['id'])){
				correct_user($user['id'], $arr['screen_name']);
			}
			continue;
		}
		
		$res = preg_match('/weibo.com\/(.+)/', $weibo, $mat);
		if(isset($mat[1])){	//if hit
			correct_user($user['id'], $mat[1]);
		}
	}
	
	function correct_user($user_id, $screen_name){
		query("update users set weibo='$screen_name' where id=$user_id");
		echo "$user_id => $screen_name \n";
	}
?>