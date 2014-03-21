<?php
use NGO20Map\Local;

class LocalAction extends Action{
    function index($name=0){
        $local_map_model = new LocalMapModel();
        $user_model = new UsersModel();
        $local_map = $local_map_model->where(array(
            'identifier' => $name,
        ))->find();
        $admin_user = $user_model->find($local_map['admin_id']);
        $map_config = json_decode($local_map['config'], true);
        $this->assign('local_map', $local_map);
        $this->assign('map_config', $map_config);
        $this->assign('admin_user', $admin_user);
        $this->display();
    }
    
    
    
    
    public function manage(){
    	$local_map_model = new LocalMapModel();
		
		import("ORG.Util.TBPage");
		$listRows = C('ADMIN_ROW_LIST');
		$local_map_count = $local_map_model->where('enabled=1')->count();
		$Page = new TBPage($news_count,$listRows);
		$local_map_result = $local_map_model->order('id desc')->limit($Page->firstRow.','.$listRows)->select();
		
		$page_bar = $Page->show();
    
    	$this->assign('local_result', $local_map_result);
    	$this->assign('page', $page_bar);
        $this->display();
    }
    
    public function add_map(){
    	$this->assign('action', 'insert');
    	$this->display();
    }

    public function insert(){
    	$local_map_model = new LocalMapModel();
		$local_map_model->create();
		$local_map_model->config = json_encode(C('DEFAULT_LOCAL_CONFIG'));
		$local_map_model->add();

		$this->redirect('manage');
    }

    public function edit($id){
    	$local_map_model = new LocalMapModel();
    	$local_map = $local_map_model->find($id);

        $user_model = new UsersModel();
        $user = $user_model->find($local_map['admin_id']);
        $local_map['user_name'] = $user['name'];

    	$this->assign('local_map', $local_map);
    	$this->assign('action', 'save');
    	$this->display('add_map');
    }

	public function save(){
		$news_model = new LocalMapModel();
		$news_model->create();
		$news_model->save();

		$this->redirect('manage');
	}

    public function delete_map($id){
        $local_map_model = new LocalMapModel();
        $local_map_model->delete($id);

        $this->redirect('manage');
    }
    
    // post content
    
    function post_add(){
        $local_map_model = new LocalMapModel();
    	$local_map = $local_map_model->find($id);
        
        $this->assign('local_map', $local_map);
        $this->display();
    }
    
    function post_insert(){
        $local_content_model = new LocalContentModel();
        $local_content_model->add(array(
            'local_id' => $_POST['local_id'],
            'name' => $_POST['name'],
            'content' => $_POST['description'],
            'key' => $_POST['key'],
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ));
        $this->redirect('post_view');
    }
    
    function post_view($local_id, $post_id){
        $local_content_model = new LocalContentModel();
        $post = $local_content_model->find($post_id);
        
        $this->assign('local_id', $local_id);
        $this->assign('post', $post);
        $this->display();
    }

    /*
        @param $local_id: the id of local map in local_map table;
        @param $content_id: the category of the content: the "key" field of the local_content table
    */
     public function post_list($local_id, $content_id){
        $local_map_model = new LocalMapModel();
        $local_content_model = new LocalContentModel();

        $local_map = $local_map_model->find($local_id);
        $query_map = array(
                'local_id' => $local_id,
                'key' => $content_id,
            );
        if($local_map['admin_id'] != user('id')){
            $is_local_admin = true;
            $query_map['is_checked'] = 1;   // if the user is not the admin, only display checked results.
        }
        else{
            $is_local_admin = false;
        }
        import("ORG.Util.TBPage");
		$listRows = C('ADMIN_ROW_LIST');
        $post_count = $local_content_model->where($query_map)->count();
        $posts = $local_content_model->where($query_map)->select();
        
        
        $this->assign('local_map', $local_map);
        $this->display();
    }
    
    public function _post_widget($local_id, $module_info){
        $local_content_model = new LocalContentModel();
        $results = $local_content_model->where(array(
            'local_id'=>$local_id,
            'key'=>$module_info['id'],
        ))->limit(C('RECORD_PER_POST_WIDGET'))->select();
        
        $this->assign('local_id', $local_id);
        $this->assign('module_info', $module_info);
        $this->assign('results', $results);
        $this->display('_post_widget');
        
    }

    public function _content_sidebar($local_id){
        $local_map_model = new LocalMapModel();
        $local_map = $local_map_model->find($local_id);
        $map_config = json_decode($local_map['config'], true);
        
        $this->assign('modules', $map_config['modules']);
        $this->assign('local_map', $local_map);
        $this->display('_content_sidebar');
    }

}
?>