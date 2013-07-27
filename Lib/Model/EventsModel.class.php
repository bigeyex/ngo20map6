<?php

class EventsModel extends Model{
	// if there is image attached with the event
	// return the url of image with 'image' field
	// else: no 'image' field
	public function find_with_image($id){
		$event = $this->find($id);
		if($event){
			$media_model = new MediaModel();
			$media = $media_model->where(array('event_id'=>$event['id'], 'type'=>'image'))->find();
			if($media){
				$event['image'] = $media['url'];
			}
		}
		return $event;
	}

	// overwrite the create function of thinkphp
	// 1. assign '' to begin_time, end_time to avoid 1907 problem
	// 2. set the default user_id to the current user_id if not predefined
	// 3. assign create_time, edit_time
	public function create(){
		$result = parent::create();

		if(!isset($_POST['begin_time'])){
			$this->begin_time = '';
		}
		if(!isset($_POST['end_time'])){
			$this->end_time = '';
		}
		if(!isset($_POST['user_id'])){
			$this->user_id = user('user_id');
		}
		if(!isset($_POST['create_time'])){
			$this->create_time = date('Y-m-d H:i:s');
		}
		$this->edit_time = date('Y-m-d H:i:s');
		if(user('is_admin') || user('is_vip') ){
        	$event_model->is_checked = 1;
        }
        else{
        	$event_model->is_checked = 0;
        }
        if(!isset($_POST['type'])){
        	$this->type = user('type');
        }

		return $result;
	}

	public function create_tags($this_id){
		if(!isset($_POST['tags']))return;

		$tag_model = new TagsModel();
		$tag_model-> clear_tags_for_event($this_id);
        $tag_names = explode(',', $_POST['tags']);
        foreach($tag_names as $tag){
        	$tag_model->assign_tag_to_event($tag, $this_id);
        }
	}

	// find <img> tag within content and save them to db
	public function create_image_records($this_id){
        $media_model = new MediaModel();
        $media_model->where(array('event_id'=>$this_id))->delete();
        $match_all = array();
        preg_match_all('/thumb600_([^\"]+)\\"/i', $_POST['description'], $match_all);
        foreach ($match_all[1] as $url) {  
            $media_model->add(array('url'=>$url, 'event_id'=>$this_id, 'type'=>'image'));
        }
	}

	// if the user with certain name exist
	// set its id to the 'user_id' field of this event
	public function set_creator_by_name($name){
		$user_model = new UsersModel();
		$creator = $user_model->where(array('name' => $name))->find();
		if(!$creator){
			return false;
		}
		$this->user_id = $creator['id'];
		return true;
	}
}

?>