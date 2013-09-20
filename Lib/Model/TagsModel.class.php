<?php

class TagsModel extends Model{
	
	public function assign_tag_to_event($tag_name, $event_id){
		$tagmap_model = M('tagmap');
		$existing_tag = $this->where(array('name'=>$tag_name))->find();
    	if(!$existing_tag){
    		$tag_id = $this->add(array('name' => $tag_name));
    	}
    	else{
    		$tag_id = $existing_tag['id'];
    	}
    	$tagmap_model->add(array('tag_id'=>$tag_id, 'event_id'=>$event_id));
	}

	public function clear_tags_for_event($event_id){
		$tagmap_model = M('tagmap');
		$tagmap_model->where(array('event_id'=>$event_id))->delete();
	}
}

?>