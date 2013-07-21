<?php
// 本类由系统自动生成，仅供测试用途
class AccountAction extends Action {

	public function login(){
		$account_model = new AccountsModel();
		if($account_model->login($_POST['email'], $_POST['password'])){

			//处理自动登录
            if(!empty($_POST['remember'])){
            	$login_email = user('email');
            	$login_key = md5($login_email . rand(0,10000) .time() . SALT_KEY);
            	$login_token = md5($login_key . SALT_KEY . user('password'));
            	setcookie("ngo20_login_email", $login_email, time()+3600*24*14);
            	setcookie("ngo20_login_key", $login_key, time()+3600*24*14);
            	setcookie("ngo20_login_token", $login_token, time()+3600*24*14);
            }

            $this->redirect('User/home/');
            
        }
		else{
			//login failed
			flash('用户名或密码不正确');
		}
	}

	public function logout(){
		unset($_SESSION['login_user']);
        unset($_SESSION['last_page']);
        unset($_SESSION['last_page']);
        unset($_SESSION['api']);
        setcookie("ngo20_login_email", "", time()-3600);
        setcookie("ngo20_login_key", "", time()-3600);
        setcookie("ngo20_login_token", "", time()-3600);
        $this->redirect('Index/index');
	}

}

?>