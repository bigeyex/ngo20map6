<?php

class AccountsModel extends Model{

	public function login($email, $pwd){
        $result = $this->where(array('email' => $email, 'password' => md5($pwd)))->find();
        if(!$result){
            return false;
        }
        elseif($result['enabled'] == 0){
            return false;
        }
        else{
            //login successfully
            //fetch other user information
            $user_model = new UsersModel();
            $user_data = $user_model->where(array('account_id'=>$result['id']))->find();

            $_SESSION['login_user'] = array_merge($user_data, $result);
            $_SESSION['login_user']['id'] = $user_data['id'];
            $this->where(array('id'=>$result['id']))->data(array('last_login'=>date('Y-m-d h:i:s')))->save();
            $this->query("update users set login_count=login_count+1 where id=$user_id");
            return true;
        }
	}

    public function add_user($post){
        $post['password'] = md5($post['password']);
        $id = $this->add($post);
        $this->login($post['email'], $post['password']);
        return $id;
    }
}

?>