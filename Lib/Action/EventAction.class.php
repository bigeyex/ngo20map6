<?php
// 本类由系统自动生成，仅供测试用途
class EventAction extends Action {

    public function manage(){
        $event_model = new EventsModel();
        $results = $event_model->select_by_user(user('id'));
        $this->assign('results', $results);
        $this->display();
    }

    public function view($id){
        $event_model = new EventsModel();
        $user_model = new UsersModel();
        $recommend_model = new RecommendModel();
        $media_model = new MediaModel();
        $event = $event_model->find($id);
        $user = $user_model->find($event['user_id']);
        $recommended_users = $recommend_model->users_by_event($id);
        $recommended_events = $recommend_model->events_by_event($id);
        $images = $media_model->select_images_by_event($id);
        $this->assign('images', $images);
        $this->assign('event', $event);
        $this->assign('user', $user);
        $this->assign('rec_users', $recommended_users);
        $this->assign('rec_events', $recommended_events);
        $this->display();
    }

	public function get_detail($id){
		$event_model = D('Events');
		$event = $event_model->find($id);
		if($event){
			$this->ajaxReturn(array('description'=>strip_tags($event['description'])),'JSON');
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
        $event = array(
            'begin_time' => date('Y-m-d'),
            'end_time' => date('Y-m-d'),
            );

        $this->assign('event', $event);
        $this->assign('event_type', user('type'));
        $this->assign('target_url', 'insert');
		$this->display();
	}

    public function edit($id){
        $event_model = new EventsModel();
        $event = $event_model->find($id);
        $event['begin_time'] = date('Y-m-d',strtotime($event['begin_time']));
        $event['end_time'] = date('Y-m-d',strtotime($event['end_time']));

        $this->assign('event', $event);
        $this->assign('target_url', 'save');
        $this->display('add');
    }

    public function save(){
        $event_model = new EventsModel();
        $this_id = intval($_POST['id']);
        $old_event = $event_model->find($this_id);
        if($old_event['longitute']!=$_POST['longitute'] || $old_event['latitude']!=$_POST['latitude']){
            $map_data_model = D('MapData');
            $map_data_model->invalidate_tile($old_event['longitute'], $old_event['latitude']);
            $map_data_model->invalidate_tile($_POST['longitute'], $_POST['latitude']);
        }
        $event_model->create();
        if(!user('is_admin')){
            $event_model->user_id = user('id');
        }
        if(!check_model()){
            flash('您提交的内容中可能有不合适的地方，请重新编辑');
        }


        $event_model->save();
        $event_model->create_tags($this_id);
        $event_model->create_image_records($this_id);
       
        flash('成功更新活动信息');
        
        $this->redirect('Event/view', array('id'=>$this_id));
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
            flash('您提交的内容中可能有不合适的地方，请重新编辑');
        }

        if($error_message != ''){
            $this->assign('event', $_POST);
            $this->assign('target_url', 'insert');
            flash($error_message);
            $this->display('add');
            return;
        }

        if(!user('is_admin')){
            $event_model->type = user('type');
        }
        
        
        // case: the admin want to assign this event to another person (rare)
        if($_POST['creator'] && user('is_admin')){
        	
        }
        else{
            $event_model->user_id = user('id');
        }

        $this_id = $event_model->add();
        $event_model->create_tags($this_id);
        $event_model->create_image_records($this_id);

        $map_data_model = D('MapData');
        $map_data_model->invalidate_tile($_POST['longitute'], $_POST['latitude']);
       
        flash('事件已成功添加');
        
        $this->redirect('Event/view', array('id'=>$this_id));
	}


}

?>