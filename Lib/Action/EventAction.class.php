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

	public function preview($id){
		$event_model = new EventsModel();
		$event = $event_model->find_with_image($id);
		if($event){
			$this->assign('event',$event);
			$this->display();
		}
	}

	public function add(){
		$this->display();
	}


}

?>