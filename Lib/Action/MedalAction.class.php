<?php

class MedalAction extends Action{
	public function index(){
		$medal_model = M('Medal');
		$medals = $medal_model->select();

		$this->assign('medals', $medals);
		$this->display();
	}

	public function add(){
		$this->assign('action', "insert");
		$this->display();
	}

	public function insert(){
		$medal_model = M('Medal');
		$medal_model->create();
		$medal_model->add();

		$this->redirect('index');
	}

	public function edit(){
		$medal_model = M('Medal');
		$medal = $medal_model->find($_GET['id']);
		$img_selected = array();
		$img_selected[$medal['image']] = selected;

		$this->assign('img_selected', $img_selected);
		$this->assign('medal', $medal);
		$this->assign('action', "save");
		$this->display('add');
	}

	public function save(){
		$medal_model = M('Medal');
		$medal_model->create();
		$medal_model->save();

		$this->redirect('index');
	}

	public function delete(){
		$medal_model = M('Medal');
		$medal_model->where(array('id' => $_GET['id']))->delete();

		$this->redirect('index');
	}

	public function bearers(){
    	$user_model = M('Users');
    	$medal_model = M('Medal');
    	$medal = $medal_model->find($_GET['id']);
    	
    	//从session中读取搜索条件
		if(isset($_SESSION['admin_bearers_condition']) && !isset($_GET['clear'])){
			$admin_bearers_condition = $_SESSION['admin_bearers_condition'];
		}
		else{
			$_GET['medal_id'] = $_GET['id'];
			$admin_bearers_condition = array('type'=>'all');
		}
		//用传入的搜索条件覆盖现有的搜索条件
		//XXX: sql injection prevention relies on PHP settings. see get_magic_quotes_gpc()
		foreach($_GET as $key=>$value){
			$admin_bearers_condition[$key] = $value;
		}
		if($_GET['q'] == 'all'){
			$admin_bearers_condition['q'] = '';
		}
		//保存搜索条件
		$_SESSION['admin_bearers_condition'] = $admin_bearers_condition;
		extract($admin_bearers_condition);
		
		//筛选
		$where_clause = array();
		if($type != 'all'){
			$where_clause['type'] = $type;
		}
		if(!empty($q)){
			$where_clause['name'] = array('like', "%$q%");
		}
		
		import("ORG.Util.TBPage");
		$listRows = C('ADMIN_ROW_LIST');
		$user_count = $user_model->where($where_clause)->count();
		$Page = new TBPage($user_count,$listRows);
		$user_model->field("*, (select count(*) from medalmap where user_id=users.id and medal_id=$medal_id) is_granted");
		$user_result = $user_model->where($where_clause)->order('create_time desc')->limit($Page->firstRow.','.$listRows)->select();
		
		$page_bar = $Page->show();

		$bearer_info = $this->get_bearer_names($medal['id']);
		$bearer_count = $bearer_info[0];
		$bearer_names = $bearer_info[1];
    
    	$this->assign('medal', $medal);
    	$this->assign('q', $q);
    	$this->assign('check', $check);
    	$this->assign('type', $type);
    	$this->assign('user_result', $user_result);
    	$this->assign('page', $page_bar);
    	$this->assign('bearer_count', $bearer_count);
    	$this->assign('bearer_names', $bearer_names);
        $this->display();
	}

	public function get_bearer_names($medal_id = 0){
		if($medal_id == 0) $medal_id = $_GET['id'];
		//get all the names
		$model = new Model();
		$bearers = $model->query("select name from users where exists (select id from medalmap where medal_id=$medal_id and user_id=users.id)");
		$bearer_count = count($bearers);
		$bearer_list = array();
		foreach($bearers as $bearer){
			$bearer_list[] = $bearer['name'];
		}
		$bearer_names = implode(', ', $bearer_list);

		if(ACTION_NAME == "get_bearer_names"){
			$this->assign('bearer_count', $bearer_count);
    		$this->assign('bearer_names', $bearer_names);
			$this->display();
		}
		else{
			return array($bearer_count, $bearer_names);
		}
	}


	public function grant(){
		$medal_map_model = M('Medalmap');
		$medal_map_model->medal_id = $_GET['id'];
		$medal_map_model->user_id = $_GET['uid'];
		if($medal_map_model->add()){
			echo "ok";
		}
		else{
			echo $medal_map_model->getDbError();
		}
	}

	public function ungrant(){
		$medal_map_model = M('Medalmap');
		$medal_map_model->where(array('user_id'=>$_GET['uid'], 'medal_id'=>$_GET['id']))->delete();
		echo 'ok';
	}
	
}
?>