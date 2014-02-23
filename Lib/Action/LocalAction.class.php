<?php
class LocalAction extends Action{
    function index(){
        $this->display();
    }
    
    function add_content(){
        $this->display();
    }
    
    function insert_content(){
        
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

    public function delete($id){
        $news_model = new NewsModel();
        $news_model->delete($id);

        $this->redirect('manage');
    }


}
?>