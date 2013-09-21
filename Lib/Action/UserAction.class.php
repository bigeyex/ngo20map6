<?php
// 本类由系统自动生成，仅供测试用途
class UserAction extends Action {

	public function get_detail($id){
		$user_model = D('Users');
		$user = $user_model->find($id);
		if($user){
			$this->ajaxReturn(array('description'=>strip_tags($user['introduction'])),'JSON');
		}
	}

	public function preview($id){
		$user_model = new UsersModel();
        $medal_model = new MedalModel();
		$user = $user_model->find($id);
        $medals = $medal_model->select_by_userid($id);

		if($user){
            $this->assign('medals', $medals);
			$this->assign('user',$user);
			$this->display();
		}
	}

    public function edit() {
        if($_SESSION['login_user']['is_admin'] && isset($_GET['id'])){
            $id = $_GET['id'];
        }else{
            $id = $_SESSION['login_user']['id'];
        } 
        $user_model = new UsersModel();
        $user_data = $user_model->find($id);
        $type = $user_data['type'];
        $completeness = $user_model->completeness();
        $this->assign('complete_100',$completeness);
        $this->assign('complete_pixel',$completeness/100*162);
        $this->assign('type', $type);
        $this->assign('user', $user_data);
        $this->display();
    }

    public function view($id){
        $user_model = new UsersModel();
        $event_model = new EventsModel();
        $recommend_model = new RecommendModel();
        $weibo_model = new WeiboModel();
        $user = $user_model->find($id);
        $recommended_users = $recommend_model->users_by_user($id);
        $events = $event_model->select_by_user($id);
        $weibo = $weibo_model->select_weibo_by_user($id);
        $this->assign('user', $user);
        $this->assign('events', $events);
        $this->assign('rec_users', $recommended_users);
        $this->assign('weibo', $weibo);
        $this->display();
    }

	public function home(){
        $event_model = new EventsModel();
        $this->assign('event_count', $event_model->count_by_user(user('id')));
		$this->display();
	}

    public function home_recommend(){
        $recommend_model = new RecommendModel();
        $this->assign('results', $recommend_model->recommend());
        $this->display();
    }

	public function insert(){
		$user_model = new UsersModel();
		$account_model = new AccountsModel();

        
        //检查验证码是否一致
        if($_SESSION['verify'] != strtolower($_POST['verify'])) {
        	flash('验证码不一致');
        	$_SESSION['last_form'] = $_POST;
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        if(empty($_POST['work_field']) && empty($_POST['expertise'])) {
            flash('关注领域不能为空');
            $_SESSION['last_form'] = $_POST;
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        if(!check_model()) {
            flash('您提交的内容中可能有不合适的地方，请重新编辑');
            $_SESSION['last_form'] = $_POST;
            $this->redirect('register');
        }

        if(empty($_POST['type'])){
            $_POST['type'] = 'ngo';
        }
        
        $_POST['create_time'] = date('Y-m-d H:i:s');
        if(!$_SESSION['login_user']['is_admin']){
        	$_POST['is_admin'] = 0;
        }
        if($_POST['type']=="ind") {
            $_POST['is_checked'] = 1;
        }

        $user_model->create();
        $account_id = $account_model->add_user($_POST);
        if($account_id){
        	$user_model->account_id = $account_id;
        	$user_model->add();
            $account_model->login($_POST['email'], $_POST['password']);
        	$this->redirect('home');
        }
        else{
        	$this->redirect('register');//写好User/home后定位到该目标
        }   
	}

    public function register(){
        if(isset($_SESSION['last_form'])){
            $this->assign('user', $_SESSION['last_form']);
        }
        $this->display();
    }

    public function save() {
        $user=M('Users');
        $user->create();
        
        //如果不是管理员或者没有传id，则改自己的资料
        if(!isset($_POST['id']) || !$_SESSION['login_user']['is_admin']){
            $user->id = $_SESSION['login_user']['id'];
        }
        else{
            $user->id = $_POST['id'];
        }
        
        if(!$_SESSION['login_user']['is_admin']){
            $user->is_admin = 0;
        }

        if(!check_model()) {
            flash('您提交的内容中可能有不合适的地方，请重新编辑');
            $this->display('newUser');
        }else {
            $user->save();
            $result = $user->where(array('id' => $_SESSION['login_user']['id']))->find();
            if($result){
                $_SESSION['login_user'] = $result;
                flash('您的个人信息已修改成功！');
            }
            else{
                flash('信息更新失败');
            }
            $this->redirect('edit');//写好User/home后定位到该目标
        }

        $this->redirect('edit');//写好User/home后定位到该目标
    }

}

?>