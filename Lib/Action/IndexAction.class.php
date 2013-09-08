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
            query_map(array('type'=>'exngo', 'user_fields'=>'count(*) cnt'));
        $csr_num_record = $map_data_model->
            query_map(array('type'=>'excsr', 'user_fields'=>'count(*) cnt'));
        $case_num_record = $map_data_model->
            query_map(array('type'=>'case', 'user_fields'=>'count(*) cnt'));
        $this->assign('ngo_num', $ngo_num_record[0]['cnt']);
        $this->assign('csr_num', $csr_num_record[0]['cnt']);
        $this->assign('case_num', $case_num_record[0]['cnt']);

        $this->display();
    }

    public function load_weibo(){
    	$weibo_model = D('Weibo');
    	$this->ajaxReturn($weibo_model->get_recent_weibo(), 'json');
    }

}

?>