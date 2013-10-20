<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {

    public function index(){
    	// get medal list
    	$medal_model = new MedalModel();
    	$this->assign('medals', $medal_model->select());

        // get all the numbers
        $map_data_model = D('MapData');
        $ngo_num_record = $map_data_model->
            query_number(array('type'=>'exngo'));
        $csr_num_record = $map_data_model->
            query_number(array('type'=>'excsr'));
        $case_num_record = $map_data_model->
            query_number(array('type'=>'case'));
        $this->assign('ngo_num', $ngo_num_record);
        $this->assign('csr_num', $csr_num_record);
        $this->assign('case_num', $case_num_record);


        $news_model = new NewsModel();
        $news_list = $news_model->limit(20)->select();

        $this->assign('news_list', $news_list);
        $this->display();
    }

    public function load_weibo(){
    	$weibo_model = D('Weibo');
    	$this->ajaxReturn($weibo_model->get_recent_weibo(), 'json');
    }

}

?>