<?php

class EventsModel extends Model{
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
}

?>