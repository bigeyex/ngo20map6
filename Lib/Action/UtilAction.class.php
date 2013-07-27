<?php
// 本类由系统自动生成，仅供测试用途
class UtilAction extends Action {

    public function captcha(){
    	import('ORG.Util.ValidateCode');
        $_vc = new ValidateCode();      //实例化一个对象
        $_vc->doimg();
        $_SESSION['verify'] =strtolower($_vc->getCode());//验证码保存到SESSION中
    }

    

}

?>