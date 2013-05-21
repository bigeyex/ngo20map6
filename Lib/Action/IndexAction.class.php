<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {

    public function index(){
        $this->display();
    }

    public function load_weibo(){
    	$weibo_model = D('Weibo');
    	$this->ajaxReturn($weibo_model->get_recent_weibo(), 'json');
    }

}

?>