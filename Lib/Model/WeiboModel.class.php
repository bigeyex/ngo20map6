<?php

class WeiboModel extends Model{

	public function get_recent_weibo($count=20){

		//load recent weibo group by user
		//load top 1 most recent weibo of each user 
		$weibo = $this->query('select * from weibo group by user_id order by post_time desc limit '.$count);
		
		//fetch user information using "in"
		$user_ids = array();
		foreach ($weibo as $w) {
			$user_ids[] = $w['user_id'];
		}
		$users = $this->query('select id,longitude,latitude from users where id in (' . implode(',', $user_ids) . ')');
		$user_id_map = array();
		foreach ($users as $user) {
			$user_id_map[$user['id']] = $user;
		}

		//attach user infromation to weibo
		for($i=0;$i<count($weibo);$i++){
			$uid = $weibo[$i]['user_id'];
			$weibo[$i]['longitude'] = $user_id_map[$uid]['longitude'];
			$weibo[$i]['latitude'] = $user_id_map[$uid]['latitude'];
		}

		//return the result;
		return $weibo;
	}
}


?>