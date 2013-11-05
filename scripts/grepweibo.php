<?php
	require('db.php');
	// define parameters
	$collect_count = 30;
	$last_collect = config('last_weibo_collect');
	$access_token = '2.00vDZNLCN7hJiB566636c706wty4rD';
	
	date_default_timezone_set('Asia/Shanghai');
	
	
	if($last_collect == null){
		$last_collect = 0;
	}
	
	//1. get user list
	
	$users = query("select * from users where is_checked=1 and type='ngo' and weibo != '' and weibo_provider='新浪微博' and id>$last_collect order by id limit $collect_count");
	//   set new collect count
	if(count($users) < $collect_count){
		config('last_weibo_collect', 0);
	}
	else{
		config('last_weibo_collect', $users[$collect_count-1]['id']);
	}
	
	//2. foreach user:
	foreach($users as $user){
	
		//3. get weibo of this user
		$screen_name = $user['weibo'];
		$json = file_get_contents("https://api.weibo.com/2/statuses/user_timeline.json?access_token=$access_token&screen_name=$screen_name&feature=1");
		$arr = json_decode($json, true);
		
		if(!isset($arr['statuses'])){	// if exceed weibo api quota
			config('last_weibo_collect', $user['id']);
			break;
		}
		
		//4. store into database	
		foreach($arr['statuses'] as $status){
			if($status['user']['uid'] == 1998038407 || $status['user']['screen_name']=='乌宇'){
				//echo 'bad user';
				break;
			}
			
			$sql = "select id from weibo where mid='" . $status['mid'] . "'";
			$rec_count = query($sql);
			if(count($rec_count)>0){
				break;
			}
			
			$sql = "insert into weibo (user_id, mid, provider, content, lon, lat, image, retweet_count, comment_count, post_time, avatar_img, weibo_name) values (".$user['id']. ",'" . $status['mid'] . "'" .
					",'weibo','".x($status['text'])."','".$status['geo']['coordinates'][1]."','".$status['geo']['coordinates'][0]."','".$status['bmiddle_pic']."',".
					$status['reposts_count'].",".$status['comments_count'].",'" . date('Y-m-d H:i:s',strtotime($status['created_at'])). 
					"','" . $status['user']['profile_image_url'] . "','".$status['user']['screen_name']."')";
			query($sql);
		}
	
	}
	
	function x($str){
		return mysql_real_escape_string($str);
	}
	
	
	function config($key, $value = null){
		
		$record = query("select v from settings where k='$key'");
	
		if($value === null){	//to get a value
			if(count($record)>0){
				return $record[0]['v'];
			}
			else{
				return null;
			}
		}
		else{	//to set a value
			if(count($record)>0){
				query("update settings set v='$value' where k='$key'");
			}
			else{
				query("insert into settings (k, v) values ('$key', '$val')");
			}
			return null;
		}
	}
?>