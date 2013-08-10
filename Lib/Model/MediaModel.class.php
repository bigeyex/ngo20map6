<?php

class MediaModel extends Model{
	public function select_images_by_event($id){
		$result = $this->where(array(
			'type' => 'image',
			'event_id' => $id
			))->select();
		if(!$result) $result = array();
		return $result;
	}
}

?>