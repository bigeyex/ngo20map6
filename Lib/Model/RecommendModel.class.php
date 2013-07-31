<?php

class RecommendModel{

	public function recommend($types=null, $page=1){
    	$geo_weight = 1;				// weight of geo location
    	if($res_tags != 0){
			$geo_weight = 20;	// if query volunteer info., care more about location.
		}
    	$category_weight = 200;		// weight of category
    	$date_weight = 0;			// weight of time
    	$record_per_page = 20;

    	if($types === null){
    		$types = array('ngo', 'csr', 'case');
    	}
    	$user_types = $types;
    	$event_types = $types;
    	if(in_array('csr', $types)){
    		array_push($event_types, 'ind');
    	}
    	
    	$myself = $_SESSION['login_user'];
    	
    	$my_longitude = $myself['longitude'];
    	$my_latitude = $myself['latitude'];
    	$my_categories = explode(' ',$myself['work_field']);

    	$limit_start = ($page-1) * $record_per_page;
    	
    	$model = new Model();
    	$sql_user_fields = "select id, name, introduction, type, create_time, 'user' model, image";
    	$sql_event_fields = "select id, name, description, type, create_time, 'event' model, '' image";
    	$sql_geo_fields = ", $geo_weight*(abs(longitude-$my_longitude)+abs(latitude-$my_latitude))";
    	$sql_user_cate = $sql_event_cate = '';
		foreach($my_categories as $category){
			$sql_user_cate .= "-$category_weight*if(work_field like '%$category%',1,0) score ";
			$sql_event_cate .= "-$category_weight*if(item_field like '%$category%',1,0) score ";
		}
		$sql_user = $sql_user_fields . $sql_geo_fields . $sql_user_cate . "from users where type in ('" . implode("','", $user_types)."') and enabled=1 and is_checked=1";
		$sql_event = $sql_event_fields . $sql_geo_fields . $sql_event_cate . "from events where type in ('" . implode("','", $event_types)."') and enabled=1 and is_checked=1";
		$sql = "select * from ($sql_user) t1 union ($sql_event) order by score,create_time desc limit $limit_start, $record_per_page";
    	$result = $model->query($sql);

    	// attach photo url to events
    	$media_model = new MediaModel();

    	for($i=0;$i<count($result);$i++){
    		if($result[$i]['model'] == 'event'){
    			$result[$i]['image'] = $media_model->where(array('event_id'=>$result[$i]['id']))->find();
    		}

    	}

    	return $result;
    }



}

?>