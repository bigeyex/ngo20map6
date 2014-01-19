<?php
  

import("ORG.OAuth.ThinkOAuth2");  
  
class OAuthAction extends Action {  
      
    private $oauth = NULL;  
  
    function _initialize(){  
          
        header("Content-Type: application/json");  
        header("Cache-Control: no-store");  
        $this -> oauth = new ThinkOAuth2();  
  
    }  
      
    public function index(){  
          
        header("Content-Type:application/json; charset=utf-8");  
        $this -> ajaxReturn(null, 'oauth-server-start', 1, 'json');  
          
    }  
      
    public function access_token() {  
          
        $this -> oauth -> grantAccessToken();  
  
    }  
      
    //权限验证  
    public function authorize() {  
        $_SESSION['OAUTH_AUTH_PARAMS'] = $this -> oauth -> getAuthorizeParams();
        if ($_POST) {  
            // use this line to grant access
            $this -> oauth -> finishClientAuthorization($_POST["accept"] == "Yep", $_POST);  
            return;  
        }  
          
        ///表单准备  
        $auth_params = $this -> oauth -> getAuthorizeParams();  
        $this -> assign("auth_params", $auth_params); 
        $this->display();  
  
    }  
      
    public function addclient() {  
          
        if ($_POST && isset($_POST["client_id"]) &&  
         isset($_POST["client_secret"]) &&   
            isset($_POST["redirect_uri"])) {  
                  
            $this -> oauth -> addClient($_POST["client_id"], $_POST["client_secret"], $_POST["redirect_uri"]);  
            return;  
        }  
          
        $this->display();  
    }  
}  