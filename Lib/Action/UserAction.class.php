<?php
// 本类由系统自动生成，仅供测试用途
class UserAction extends Action {

	public function get_detail($id){
		$user_model = D('Users');
		$user = $user_model->find($id);
		if($user){
			$this->ajaxReturn(array('description'=>$user['introduction']),'JSON');
		}
	}


}

?>