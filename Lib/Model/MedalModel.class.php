<?php

class MedalModel extends Model{
	public function select_by_userid($id){
		$medalmap = M('Medalmap');
		$map_records = $medalmap->where(array('user_id'=>$id))->select();
		if(!$map_records)$map_records=array();
		$medal_ids = array();
		foreach ($map_records as $map_record) {
			$medal_ids[] = $map_record['medal_id'];
		}
		return $this->where(array('id'=>array('in', $medal_ids)))->select();
	}

	public function select_user_ids_by_medal_id($medal_id){
		$medalmap = M('Medalmap');
		$map_records = $medalmap->where(array('medal_id'=>$medal_id))->select();
		if(!$map_records)$map_records=array();
		$user_ids = array();
		foreach ($map_records as $map_record) {
			$user_ids[] = $map_record['user_id'];
		}
		return $user_ids;
	}

	public function select_as_assoc_array(){
		$medals = $this->select();
		$assoc_array = array();
		foreach($medals as $medal){
			$assoc_array[$medal['code_name']] = $medal;
		}
		return $assoc_array;
	}
}

?>