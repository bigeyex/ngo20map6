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

    public function users_by_event($id, $type='ngo', $count=6, $has_image=true){
        $event_model = new EventsModel();
        $event = $event_model->find($id);
        return $this->recommend_users($event, $type, $count, $has_image);  
    }

    public function users_by_user($id, $type='ngo', $count=6, $has_image=true){
        $user_model = new UsersModel();
        $user = $user_model->find($id);
        return $this->recommend_users($user, $type, $count, $has_image);  
    }

    public function recommend_users($data, $type='ngo', $count=6, $has_image=true){
        $geo_weight = 10;
        $category_weight = 200; 
        $event_model = new EventsModel();
        if(!$data) return false;
        $my_longitude = $data['longitude'];
        $my_latitude = $data['latitude'];
        if($has_image){
            $sql_image = 'and image is not null';
        }
        else{
            $sql_image = '';
        }
        $sql = "select id,name,longitude,latitude,image, $geo_weight*(abs(longitude-$my_longitude)+abs(latitude-$my_latitude)) score from users where type='$type' $sql_image and longitude is not null order by score limit $count";
        $result = $event_model->query($sql);
        return $result;
    }

    public function events_by_event($id, $type='ngo', $count=6, $has_image=true){
        $geo_weight = 10;
        $category_weight = 200; 
        $event_model = new EventsModel();
        $media_model = new MediaModel();
        $event = $event_model->find($id);
        if(!$event) return false;
        $my_longitude = $event['longitude'];
        $my_latitude = $event['latitude'];
        $sql = "select id,name,longitude,latitude,description, $geo_weight*(abs(longitude-$my_longitude)+abs(latitude-$my_latitude)) score from events where type='$type' and longitude is not null order by score limit $count";
        $result = $event_model->query($sql);
        if($has_image){
            for ($i=0; $i < count($result); $i++) { 
                $result[$i]['image'] = $media_model->where(array('event_id'=>$result[$i]['id'], 'type'=>image))->find();
            }
        }
        return $result;
    }


}

?>