<?php
// 本类由系统自动生成，仅供测试用途
class UtilAction extends Action {

    public function captcha(){
    	import('ORG.Util.ValidateCode');
        $_vc = new ValidateCode();      //实例化一个对象
        $_vc->doimg();
        $_SESSION['verify'] =strtolower($_vc->getCode());//验证码保存到SESSION中
    }

    public function check_unique_email() {
        $user=M('Users');
        $email = $_GET['fieldValue'];
        $u = $user->where(array('email'=>array('eq',$email)))->count();
        if($u!=0) {
            echo json_encode(array('email', false));
        }
        else {
            echo json_encode(array('email', true));
        }
    }
    
    public function check_unique_name(){
        if(isset($_SESSION['login_user']) && $_SESSION['login_user']['name']==$_GET['fieldValue']){
            echo json_encode(array('name', true));
            return;
        }
		$user_model = M('Users');
		$user_count = $user_model->where(array('name' => $_GET['fieldValue']))->count();
		if($user_count == 0){
			echo json_encode(array('name', true));
		}
		else{
			echo json_encode(array('name', false));
		}
	}

	public function check_verify(){
        if($_SESSION['verify'] != strtolower($_GET['fieldValue'])) {
            echo json_encode(array('verify', false));
        }
        else{
            echo json_encode(array('verify', true));
        }
    }
	
	public function check_exist(){
		$user_model = M('Users');
		$user_count = $user_model->where(array('name' => $_GET['q']))->count();
		if($user_count != 0){
			echo 'ok';
		}
		else{
			echo L('用户不存在');
		}
	}

    public function upload_image(){
    	// error_reporting(0);

		include(dirname(__FILE__).'/../Util/image-cropper-config.class.php');
		include(dirname(__FILE__).'/../Util/image-cropper-function.class.php');

		if ($_GET['act'] == 'thumb') {
			preg_match('/Uploaded\/.*/', $_POST['img_src'], $matches);
			$img_src = APP_PATH . 'Public/' . $matches[0];
			$arr = array(
				'uploaddir' => $imgthumb,
				'tempdir' => $imgtemp,
				'web_dir' => $webthumb,
				'height' => $_POST['height'],
				'width' => $_POST['width'],
				'x' => $_POST['x'],
				'y' => $_POST['y'],
				'img_src' => $img_src,
				'thumb' => true,
				'fileError' => $fileError,
				'sizeError' => $sizeError,
				'maxfilesize' => $maxuploadfilesize,
				'canvasbg' => $canvasbg,
			);
			resizeThumb($arr);
			exit;
		} elseif ($_GET['act'] == 'upload') {
			
			$big_arr = array(
				'uploaddir' => $imgbig,
				'tempdir' => $imgtemp,
				'web_dir' => $webbig,
				'height' => $_POST['height'],
				'width' => $_POST['width'],
				'x' => 0,
				'y' => 0,
				'fileError' => $fileError,
				'sizeError' => $sizeError,
				'maxfilesize' => $maxuploadfilesize,
				'canvasbg' => $canvasbg,
			);


			resizeImg($big_arr);
			
		} else {
			//nothing to do here
		}
    }

    

}

?>