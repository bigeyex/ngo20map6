<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminActionclass
 *
 * @author 王全斌
 */
class AdminAction extends Action{

    public function addUser(){
        $this->display();
    }

    //彻底从数据库删除用户数据
    public function deleteUser(){
        $user_model = M('Users');
        $del = $user_model->where(array('id'=>$_GET['id']))->delete();

        $this->redirect('users', array("hold_page"=>1));
    }

    public function check(){
        $user_model = M('Users');
        $user_model->create();
        $del = $user_model->where(array('id'=>$_GET['id']));
        $del->is_checked = 1;
        $del -> save();

        $this->redirect('users', array("hold_page"=>1));
    }

    public function uncheck(){
        $user_model = M('Users');
        $user_model->create();
        $del = $user_model->where(array('id'=>$_GET['id']));
        $del->is_checked = 0;
        $del -> save();

        $this->redirect('users', array("hold_page"=>1));
    }

    public function change_type(){
        admin_only();
        $user_model = M('Users');
        $user_model->create();
        $del = $user_model->where(array('id'=>$_GET['id']));
        $del->type = $_GET['type'];
        $del->save();

        echo 'ok';
    }

    public function setvip(){
        $user_model = M('Users');
        if($_GET['status']){
            $status = 1;
        }
        else{
            $status = 0;
        }

        $user_model->where(array('id'=>$_GET['id']))->data(array('is_vip'=>$status))->save();
        $this->redirect('users', array("hold_page"=>1));
    }

    public function checkAll(){
        $user = M('Users');

        $ids=split(",",$_GET['ids']);
        $user->where(array('id'=>array('in',$ids)))->save(array('is_checked'=>1));
        setflash('ok','',L('所选用户已审核成功'));
        $this->redirect('users');
    }

    public function insert(){
        $user=M('Users');
        $user->create();

        //以下为头像上传代码
        import('ORG.Net.UploadFile');
        import('ORG.Util.Image');
        $upload = new UploadFile();
        $upload->maxSize = 3145728;
        $upload->savePath = './Public/Uploadedthumb/';
        $upload->thumb = true;
        $upload->thumbPath = './Public/UploadedAvatar/';
        $upload->thumbPrefix="thumbs_,thumbm_,thumb_";
        $upload->thumbMaxWidth = "50,150,200";
        $upload->thumbMaxHeight = "50,150,200";
        $upload->saveRule = 'uniqid';
        if(!$upload->upload()){
            $info = $upload->getUploadFileInfo();
        }
//      $user->id = $_SESSION['login_user']['id'];
        $user->image = $info[0]["savename"];
//        $user_model->save();
//        setflash('ok','成功修改用户头像','成功修改用户头像');
//        $_SESSION['login_user']['photo'] = $info[0]["savename"];
//        $this->redirect('newUser');

        $user->work_field = implode(' ',$_POST['work_field']);
        $user->password = md5($user->password);

        $user->add();
        setflash('ok','',L('用户已成功创建！'));
        $this->redirect('user');//写好User/home后定位到该目标
    }

    public function editUser(){
        $id = $_GET['id'];
        $user_model = M('Users');
        $user_data = $user_model->find($id);
        $this->assign('edit', $user_data);
        $this->display();
    }

        public function check_unique(){
        $user=M('Users');
        $user->create();
        $email = $_GET['email'];
        $u = $user->where(array('email'=>array('eq',$email)))->count();
        if($u!=0){
            echo 'false';
        }
        else{
            echo 'true';
        }
    }
    
    public function commitedit(){

        $user=M('Users');
        $user->create();

        //以下为头像上传代码
        import('ORG.Net.UploadFile');
        import('ORG.Util.Image');
        $upload = new UploadFile();
        $upload->maxSize = 3145728;
        $upload->savePath = './Public/Uploadedthumb/';
        $upload->thumb = true;
        $upload->thumbPath = './Public/UploadedAvatar/';
        $upload->thumbPrefix="thumbs_,thumbm_,thumb_";
        $upload->thumbMaxWidth = "50,150,200";
        $upload->thumbMaxHeight = "50,150,200";
        $upload->saveRule = 'uniqid';
        if(!$upload->upload()){
            $info = $upload->getUploadFileInfo();
        }
//        $user->id = $_POST['id'];//$_GET['id'];
        $user->image = $info[0]["savename"];
//        setflash('ok','成功修改用户头像','成功修改用户头像');
//        $_SESSION['login_user']['image'] = $info[0]["savename"];
//        $this->redirect('newUser');

        $user->work_field = implode(' ',$_POST['work_field']);
        if($_POST['editpassword']!='@@@@@@')
        $user->password = md5($_POST['password']);

        $user->save();
        setflash('ok','',L('用户信息已成功修改！'));
        $this->redirect('user');
    }

    public function newEvent(){
        $this->display('Event/new');
    }

    public function events(){
        admin_only();
    	$event_model = D('Events');
    	
    	//从session中读取搜索条件
		if(isset($_SESSION['admin_events_condition'])){
			$admin_events_condition = $_SESSION['admin_events_condition'];
			unset($admin_events_condition['p']);
		}
		else{
			$admin_events_condition = array('type'=>'all', 'check'=>'all');
		}
		//用传入的搜索条件覆盖现有的搜索条件
		//XXX: sql injection prevention relies on PHP settings. see get_magic_quotes_gpc()
		foreach($_GET as $key=>$value){
			$admin_events_condition[$key] = $value;
		}
		if($_GET['q'] == 'all'){
			$admin_events_condition['q'] = '';
		}
		//保存搜索条件
		$_SESSION['admin_events_condition'] = $admin_events_condition;
		extract($admin_events_condition);
		
		//筛选
		$where_clause = array();
		if($type != 'all'){
			$where_clause['type'] = $type;
		}
		if($check == 'deleted'){
			$where_clause['enabled'] = 0;
		}
		else if($check == 'pending'){
			$where_clause['is_checked'] = 0;
			$where_clause['enabled'] = 1;
		}
		else if($check == 'checked'){
			$where_clause['is_checked'] = 1;
			$where_clause['enabled'] = 1;
		}
		if(!empty($q)){
			$where_clause['name'] = array('like', "%$q%");
		}
		
		import("ORG.Util.TBPage");
		$listRows = C('ADMIN_ROW_LIST');
		$event_count = $event_model->where($where_clause)->count();
		$Page = new TBPage($event_count,$listRows);
		$event_result = $event_model->where($where_clause)->order('create_time desc')->limit($Page->firstRow.','.$listRows)->select();

        //fetch user name for each event
        $user_ids = array();
        foreach($event_result as $e){
            $user_ids[$e['user_id']] = 1;
        }
        $user_model = new UsersModel();
        $related_users = $user_model->query("select id,name from users where id in (".implode(',', array_keys($user_ids)).")");
        foreach($related_users as $r){
            $user_ids[$r['id']] = $r['name'];
        }
        for($i=0;$i<count($event_result);$i++){
            $event_result[$i]['creator_name'] = $user_ids[$event_result[$i]['user_id']];
        }



		$page_bar = $Page->show();
    
    	$this->assign('q', $q);
    	$this->assign('check', $check);
    	$this->assign('type', $type);
    	$this->assign('event_result', $event_result);
    	$this->assign('page', $page_bar);
        $this->display();
    }
    
    public function users(){
        admin_only();
    	$user_model = M('Users');
    	
    	//从session中读取搜索条件
		if(isset($_SESSION['admin_users_condition']) && !isset($_GET['clear'])){
			$admin_users_condition = $_SESSION['admin_users_condition'];
			if(isset($_GET['hold_page']) && !isset($_GET['p'])){
                $_GET['p'] = $admin_users_condition['p'];
            }
		}
		else{
			$admin_users_condition = array('type'=>'all', 'check'=>'all');
		}
		//用传入的搜索条件覆盖现有的搜索条件
		//XXX: sql injection prevention relies on PHP settings. see get_magic_quotes_gpc()
		foreach($_GET as $key=>$value){
			$admin_users_condition[$key] = $value;
		}
		if($_GET['q'] == 'all' || $_GET['q']===''){
			$admin_users_condition['q'] = '';
		}
		//保存搜索条件
		$_SESSION['admin_users_condition'] = $admin_users_condition;
		extract($admin_users_condition);
		
		//筛选
		$where_clause = array();
		if($type != 'all'){
			$where_clause['type'] = $type;
		}
		if($check == 'deleted'){
			$where_clause['enabled'] = 0;
		}
		else if($check == 'pending'){
			$where_clause['is_checked'] = 0;
			$where_clause['enabled'] = 1;
		}
		else if($check == 'checked'){
			$where_clause['is_checked'] = 1;
			$where_clause['enabled'] = 1;
		}
        else{
            $where_clause['enabled'] = 1;
        }
		if(!empty($q)){
			$where_clause['name'] = array('like', "%$q%");
		}
		
		import("ORG.Util.TBPage");
		$listRows = C('ADMIN_ROW_LIST');
		$user_count = $user_model->where($where_clause)->count();
		$Page = new TBPage($user_count,$listRows);
		$user_result = $user_model->where($where_clause)->order('create_time desc')->limit($Page->firstRow.','.$listRows)->select();
		
		$page_bar = $Page->show();
    
    	$this->assign('q', $q);
    	$this->assign('check', $check);
    	$this->assign('type', $type);
    	$this->assign('user_result', $user_result);
    	$this->assign('page', $page_bar);
        $this->display();
    }

    //事件管理函数
    public function batch(){
		
		if($_GET['type'] == 'users'){
			$model = M('Users');
			$model_word = '用户';
		}
        else{
        	$model = M('Events');
        	$model_word = '事件';
        }
        $ids=explode(",",$_GET['ids']);
        $action=$_GET['action'];
        $type=$_GET['type'];	//ATTENTION: this 'type' indicates where to redirect. Only use 'events' or 'users'

        if($action=='lock'){
            $data['is_checked']='0';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功屏蔽所选$model_word"), 'ok');
        }
        else if($action=='check'){
            $data['is_checked']='1';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash("您已成功审核所选$model_word", 'ok');
        }
        else if($action=='recovery'){
            $data['enabled']='1';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功恢复所选$model_word"), 'ok');
        }
        else if($action=='del'){
            $data['enabled']='0';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功删除所选$model_word"), 'ok');
        }
        else if($action=='erase'){
            $model->where(array('id'=>array('in',$ids)))->delete();
            flash(L("您已彻底删除所选$model_word"), 'ok');
        }
        else if($action=='add_v'){
            $data['is_vip']='1';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功加V所选$model_word"), 'ok');
        }
        else if($action=='del_v'){
            $data['is_vip']='0';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功取消加V所选$model_word"), 'ok');
        }

        $this->redirect($_GET['type']);
    }

    public function send_check_emails($user_id){
        $user_model = M("Users");
        $user = $user_model->find($user_id);
        $this->assign("mail_user", $user);
        $mail_content = $this->fetch("check_email");
        $subject = "[审核成功]中国公益2.0欢迎您使用公益地图";
        $headers = "From: 公益地图 <no-reply@ngo20.org> \n";  
        $headers .= "To-Sender: \n";  
        $headers .= "X-Mailer: PHP\n"; // mailer  
        $headers .= "Reply-To: no-reply@ngo20.org\n"; // Reply address  
        $headers .= "Return-Path: no-reply@ngo20.org\n"; //Return Path for errors  
        $headers .= "Content-Type: text/html; charset=utf-8"; //Enc-type  
        mail($user['email'], $subject, $mail_content, $headers);
    }


}
?>
