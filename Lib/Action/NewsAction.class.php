<?php

class NewsAction extends Action{



    public function index(){
    	$news_model = new NewsModel();
		
		import("ORG.Util.TBPage");
		$listRows = C('ADMIN_ROW_LIST');
		$news_count = $news_model->news()->count();
		$Page = new TBPage($news_count,$listRows);
		$news_result = $news_model->order('create_time desc')->limit($Page->firstRow.','.$listRows)->select();
		
		$page_bar = $Page->show();
    
    	$this->assign('q', $q);
    	$this->assign('check', $check);
    	$this->assign('type', $type);
    	$this->assign('news_result', $news_result);
    	$this->assign('page', $page_bar);
        $this->display();
    }

    public function add(){
    	$this->assign('action', 'insert');
    	$this->display();
    }

    public function insert(){
    	$news_model = new NewsModel();
		$news_model->create();
		$news_model->add();

		$this->redirect('index');
    }

    public function edit($id){
    	$news_model = new NewsModel();
    	$news = $news_model->find($id);

    	$this->assign('news', $news);
    	$this->assign('action', 'save');
    	$this->display('add');
    }

	public function save(){
		$news_model = new NewsModel();
		$news_model->create();
		$news_model->save();

		$this->redirect('index');
	}

    public function delete($id){
        $news_model = new NewsModel();
        $news_model->delete($id);

        $this->redirect('index');
    }


}
?>