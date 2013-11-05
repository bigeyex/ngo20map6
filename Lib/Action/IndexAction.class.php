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

    public function list_index(){
        $map_data_model = D('MapData');
        $record_per_page = C('LIST_RECORD_PER_PAGE');

        if(isset($_GET['clear_all'])){
            unset($_SESSION['list_queries']);
            $query_param = array();
        }
        else{
            $query_param = $_SESSION['list_queries'];
        }

        foreach($_GET as $k=>$v){
            if(isset($query_param[$k]) && $v == 0){
                unset($query_param[$k]);
            }
            else{
                $query_param[$k] = $v;
            }
        }

        $_SESSION['list_queries'] = $query_param;

        $user_fields="id, name, type, 'users' model, province, create_time";
        $event_fields="id, name, type, 'events' model, province, create_time";
        if(!empty($_GET['model'])){
            if($_GET['model'] == 'users'){
                $query_param['user_fields'] = $user_fields;
            }
            else if($_GET['model'] == 'events'){
                $query_param['event_fields'] = $event_fields;
            }
        }
        else{
            $query_param['user_fields'] = $user_fields;
            $query_param['event_fields'] = $event_fields;
        }

        $total_number = $map_data_model->query_number($query_param);
        if(isset($_GET['p'])){
            $page = $_GET['p'];
        }
        else{
            $page = 1;
        }
        $limit_start = ($page-1)*$record_per_page;
        $query_param['limit'] = $limit_start . ',' . $record_per_page;
        $query_param['order'] = "create_time desc";
        import("ORG.Util.PurePage");
        $Page = new PurePage($total_number,$record_per_page);
        $page_bar = $Page->show();

        $data = $map_data_model->query_map($query_param);
        $this->assign('pager', $page_bar);
        $this->assign('params', $query_param);
        $this->assign('page', $page);
        $this->assign('total_number', $total_number);
        $this->assign('result', $data);
        $this->display();
    }

    public function load_weibo(){
    	$weibo_model = D('Weibo');
    	$this->ajaxReturn($weibo_model->get_recent_weibo(), 'json');
    }

}

?>