<?php

class RelatedNgosModel extends Model{
	public function associate($user_id_or_name){
		if(!user())return false;
		$user_model = new UsersModel();
		if(is_numeric($user_id_or_name)){
			$user = $user_model->find($user_id_or_name);
			if($user){
				return $this->add(array('user_id'=>user('id'), 'related_user_id'=>$user_id_or_name, 'user_name'=>$user['name']));
			}
			else{
				return false;
			}
		}
		else{	// $user_id_or_name is not numeric => create by name
			$this->add(array('user_id'=>user('id'), 'user_name'=>$user_id_or_name));
			return true;
		}

	}
}

?>