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

        if(!isset($query_param)) $query_param = array();

        if(isset($_GET['clear_all'])){
            unset($_SESSION['list_queries']);
            $query_param = array();
        }
        else{
            $query_param = $_SESSION['list_queries'];
        }

        foreach($_GET as $k=>$v){
            if(isset($query_param[$k]) && $v === '0'){
                unset($query_param[$k]);
            }
            else{
                $query_param[$k] = $v;
            }
        }

        if(!isset($query_param['model'])) $query_param['model']='users';
        if(!isset($query_param['type'])) $query_param['type']='ngo';
        
        if(isset($query_param['province']) && $this->is_province_master($query_param['province'])){
            $is_expert_mode = true;
        }
        else{
            $is_expert_mode = false;
        }

        $_SESSION['list_queries'] = $query_param;

        $user_fields="*, 'users' model";
        $event_fields="*, 'events' model";
        if(!empty($query_param['model'])){
            if($query_param['model'] == 'users'){
                $query_param['user_fields'] = $user_fields;
            }
            else if($query_param['model'] == 'events'){
                $query_param['event_fields'] = $event_fields;
            }
        }
        else{
            $query_param['user_fields'] = $user_fields;
            $query_param['event_fields'] = $event_fields;
        }
        
        // export excel
        if(isset($_GET['export']) && $is_expert_mode){
            $result = $map_data_model->query_map($query_param);
            if($query_param['model'] == 'users'){
                $fields = array(
                    'name' => '名称',
                    'contact_name' => '联系人',
                    'public_email' => '电子邮箱',
                    'phone' => '联系电话',
                    'website' => '网站',
                    'city' => '所在城市',
                    'county' => '所在乡镇',
                    'place' => '地址',
                    'aim' => '机构使命',
                    'introduction' => '简介',
                    'work_field' => '服务领域',
                    'register_type' => '注册类型',
                    'register_year' => '注册年份',
                    'staff_fulltime' => '全职人数',
                    'staff_parttime' => '兼职人数',
                    'staff_volunteer' => '志愿者人数',
                    'financial_link' => '财务报告链接',
                );
            }
            else if($query_param['model'] == 'events'){
                $fields = array(
                    'name' => '名称',
                    'description' => '简介',
                    'contact_name' => '联系人',
                    'contact_email' => '电子邮箱',
                    'contact_phone' => '联系电话',
                    'contact_qq' => '联系qq',
                    'begin_time' => '开始时间',
                    'item_field' => '项目领域',
                    'city' => '所在城市',
                    'county' => '所在乡镇',
                );
            }
            
            $this->output_excel($fields, $result);
            return;
        }
        
        if($is_expert_mode){    // if expert mode, do display unchecked items
            $query_param['expert_mode'] = 1;
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
        if($query_param['model'] == 'users'){
            $query_param['order'] = "medal_score desc, create_time desc";
        }
        else{
            $query_param['order'] = "create_time desc";
        }
        import("ORG.Util.TBPage");
        $Page = new TBPage($total_number,$record_per_page);
        $page_bar = $Page->show();
        $data = $map_data_model->query_map($query_param);
        
        $medal_model = new MedalModel();
        $this->assign('medal_map', $medal_model->select_as_assoc_array());
        $this->assign('pager', $page_bar);
        $this->assign('params', $query_param);
        $this->assign('page', $page);
        $this->assign('total_number', $total_number);
        $this->assign('result', $data);
        $this->assign('is_expert_mode', $is_expert_mode);
        $this->display();
    }

    public function load_weibo(){
    	$weibo_model = D('Weibo');
    	$this->ajaxReturn($weibo_model->get_recent_weibo(), 'json');
    }
    
    public function act_approve_user($user_id, $target_status=1, $target_field='is_checked', $target_model='users'){
        $user = T($target_model)->find($user_id);
        if($this->is_province_master($user['province'])){
            T($target_model)->with('id', $user_id)->save(array($target_field=>$target_status));
            echo 'ok';
        }
        else{
            echo '缺乏权限';
        }
    }
    
    public function act_disapprove_user($user_id){
        $this->act_approve_user($user_id, 0);
    }
    
    public function act_approve_event($event_id){
        $this->act_approve_user($event_id, 1, 'is_checked', 'events');
    }
    
    public function act_disapprove_event($event_id){
        $this->act_approve_user($event_id, 0, 'is_checked', 'events');
    }
    
    public function act_delete_user($user_id){
        $this->act_approve_user($user_id, 0, 'enabled');
    }
    
    public function act_undelete_user($user_id){
        $this->act_approve_user($user_id, 1, 'enabled');
    }
    
    private function is_province_master($province){
        if(!user()) return false;
        if(user('is_admin')) return true;
        foreach(user('local_maps') as $local_map){
            if(province_equal($local_map['province'], $province) ) return true; 
        }
        
        return false;
    }
    
    private function output_excel($fields, $data){
        
        /** Include PHPExcel */
        require_once './Vendor/PHPExcel.php';


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("NGO20")
                     ->setLastModifiedBy("NGO20")
                     ->setTitle("NGO20Map")
                     ->setSubject("NGO20Map")
                     ->setDescription("NGO20Map")
                     ->setKeywords("NGO20Map");
                     
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
                     
        // fill in the header of excel
        $i = 0;
        foreach($fields as $field=>$name){
            $column = chr(ord('A')+$i);
            $activeSheet->setCellValue($column.'1', $name);
            $i++;
        }
        
        // fill in the content
        $i = 2;
        foreach($data as $line){
            $j = 0;
            foreach($fields as $field=>$name){
                $column = chr(ord('A')+$j);
                $activeSheet->setCellValue($column.$i, $line[$field]);
                $j++;
            }
            $i++;
        }
        
         // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="导出数据.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}

?>