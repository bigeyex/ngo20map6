<?php
// 本类由系统自动生成，仅供测试用途
class MediaAction extends Action {

	public function upload_image(){
	    import('ORG.Net.UploadFile');
	    import('ORG.Util.Image');
	    $upload = new UploadFile();
	    $upload->maxSize = 3145728;
	    $upload->savePath = './Public/Uploaded/';
	    $upload->thumb = true;
	    $upload->thumbPath = './Public/Uploadedthumb/';
	    $upload->thumbPrefix="thumb50_,thumb200_,thumb600_,thumb1000_";
	    $upload->allowExts=array('jpg','jpeg','png','gif');
	    $upload->thumbMaxWidth = "50,200,600,1000";
	    $upload->thumbMaxHeight = "50,200,600,1000";
	    $upload->saveRule = 'uniqid';
	    if($upload->upload()){
	        $info = $upload->getUploadFileInfo();
	        echo $info[0]["savename"];
	        return;
	    }
	    else{
	        echo $upload->getErrorMsg();
	    }
	}

}

?>