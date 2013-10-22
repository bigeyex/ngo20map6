<?php 

class NewsModel extends Model{

	public function news(){
		return $this->where(array('type'=>'news'));
	}

	public function videos(){
		return $this->where(array('type'=>'videos'));
	}

	public function create(){
		$data = parent::create();

		// add http to links
		$this->url = addhttp($this->url);

		// detect video types and fill in swffile
		if($this->type == 'video'){
			$this->swffile = $this->getFlashImage($this->url);
		}

		return $data;
	}

	public function getFlashImage($link)
	{
	    if (preg_match('/.swf$/i', $link)) {
	        return $link;
	    }
	    $site = $this->getSiteType($link);
	    if (!$site) {
	        return false;
	    }
	     
	    $flash = '';
	    $image = '';
	     
	    switch ($site) {
	        case 'youku':
	            preg_match("#/id_(\w+).html#i", $link, $m);
	            $vid = $m[1];
	            $flash = "http://player.youku.com/player.php/sid/{$vid}/v.swf";
	            break;
	        case 'tudou':
	            $content = file_get_contents($link);
	            if (!preg_match("#lcode = '([\w\-]+)'#i", $content, $m)){
	                preg_match("#,icode: ?'([\w\-]+)'#i", $content, $m);
	            }
	            $lcode = $m[1];
	 
	            preg_match('#iid: ?(\d+)#i', $content, $m);
	            $iid = $m[1];
	             
	            // 目录
	            if (strpos($link, '/view/')) {
	                $d = 'v';
	            } else {
	                preg_match("#tudou.com/([\w\-]+)/#i", $link, $m);
	                $d = strtolower(substr($m[1], 0, 1));
	            }
	            $flash = "http://www.tudou.com/{$d}/{$lcode}/&resourceId=0_04_05_99&iid={$iid}/v.swf";
	            break;
	        case 'qq':
	            preg_match("#vid=(\w+)#i", $link, $m);
	            $vid = $m[1];
	            $flash = "http://static.video.qq.com/TPout.swf?vid={$vid}";
	            break;
	        case '56':
	            if(!preg_match("#v_(\w+).html#i", $link, $m)){
	                preg_match("#vid\-(\w+).html#i", $link, $m);
	            }
	            $vid = $m[1];
	            $flash = "http://player.56.com/v_{$vid}.swf";
	            break;
	        case 'cntv':
	            preg_match("#/v-([\w\-]+).html#i", $link, $m);
	            $vid = $m[1];
	            $flash = "http://player.xiyou.cntv.cn/{$vid}.swf";
	            break;
	    }
	 
	     
	    return $flash;
	 
	}
	 
	// 获取网站类型
	public function getSiteType($url)
	{
	    $domain = '';
	    $pattern='#(https?)://([\w\.]+).*#i';
	    if (preg_match($pattern, $url, $matches)) {
	        $domain = $matches[2];
	    }
	 
	    if (stripos($domain, '56')) {
	        $site = '56';
	    } elseif (stripos($domain, 'tudou')) {
	        $site = 'tudou';
	    } elseif (stripos($domain, 'qq')) {
	        $site = 'qq';
	    } elseif (stripos($domain, 'cntv')) {
	        $site = 'cntv';
	    } elseif (stripos($domain, 'youku')) {
	        $site = 'youku';
	    } else {
	        $site = null;
	    }
	    return $site;
	}

}




?>