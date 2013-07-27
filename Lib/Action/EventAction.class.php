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
        $this->assign('target_url', 'insert');
		$this->display();
	}

	public function insert(){
        $event_model = new EventsModel();

        $error_message = '';

        if(!$event_model->create()){
            $error_message = '所填信息不完整';
        }
        if(!user()){
            $error_message = '请先登录';
        }
        if(!check_model()){
            setflash('您提交的内容中可能有不合适的地方，请重新编辑');
        }

        if($error_message != ''){
            $this->assign('event', $_POST);
            $this->assign('target_url', 'insert');
            flash($error_message);
            $this->display('add');
            return;
        }
        
        
        // case: the admin want to assign this event to another person (rare)
        if($_POST['creator'] && user('is_admin')){
        	if(!$event_model->set_creator_by_name($_POST['creator'])){
	        	$this->assign('event', $_POST);
	            $this->assign('target_url', 'insert');
	            setflash('error','',L('填写的创建人无效'));
	            $this->display('add');
	            return;
        	}
        }

        $this_id = $event_model->add();
        $event_model->create_tags($this_id);
        $event_model->create_image_records($this_id);
       
        flash('事件已成功添加');
        
        $this->redirect('Event/view', array('id'=>$this_id));
	}


}

?>