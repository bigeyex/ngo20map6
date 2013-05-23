<?php
// 本类由系统自动生成，仅供测试用途
class EventAction extends Action {

	public function get_detail($id){
		$event_model = D('Events');
		$event = $event_model->find($id);
		if($event){
			$this->ajaxReturn(array('description'=>$event['description']),'JSON');
		}
	}


}

?>