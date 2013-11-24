<?php

class RelatedNgosModel extends Model{
	public function select_by_user_id($user_id){
		$records = $this->where(array('user_id'=>$user_id))->select();
		$user_model = new UsersModel();
		$user_ids = array();
		foreach($records as $r){
			$user_ids[$r['related_user_id']] = true;
		}
		$sql = "select id,image from users where id in ('" . implode("','", array_keys($user_ids)) . "')";
		$user_records = $this->query($sql);
		foreach($user_records as $r){
			$user_ids[$r['id']] = $r['image'];
		}

		for($i=0;$i<count($records);$i++){
			if(isset($records[$i]['related_user_id'])){
				$records[$i]['image'] = $user_ids[$records[$i]['related_user_id']];
			}
			else{
				$records[$i]['image'] = null;
			}
		}
		return $records;
	}
}

?>