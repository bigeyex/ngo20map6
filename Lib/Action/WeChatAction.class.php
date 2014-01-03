<?php

class WeChatAction extends Action {

	public function endpoint(){
		import('ORG.Util.wechat');

		$weObj = new Wechat();
		$type = $weObj->getRev()->getRevType();
	    switch($type) {
	    		case Wechat::MSGTYPE_TEXT:
	    			$weObj->text($this->search_orgs($weObj->getRevContent()))->reply();
	    			exit;
	    			break;
	    		case Wechat::MSGTYPE_EVENT:
	    			break;
	    		case Wechat::MSGTYPE_IMAGE:
	    			break;
	    		case Wechat::MSGTYPE_LOCATION:
	    			$weObj->text($this->search_events($weObj->getRevGeo()))->reply();
	    			exit;
	    			break;
	    		default:
	    			$weObj->text("help info")->reply();
	    }

	}

	private function search_orgs($key){
		$model = new Model();
		$key = x($key);
		$sql = "select name from users where (name like '%$key%' or introduction like '%$key%') and type='ngo' and is_checked=1 order by id desc limit 5";
		$result = $model->query($sql);
		$i = 1;
		$ret = "有关".$key."的公益组织有:\n";
		foreach($result as $line){
			$ret .= $i . '. ' . $line['name'] . "\n";
			$i++;
		}
		return $ret;
	}

	private function search_events($geo){
		$model = new Model();
		$geo = x($geo);
		$longitude = $geo['x'];
		$latitude = $geo['y'];
		$sql = "select name from events where type='ngo' and longitude is not null and is_checked=1 order by (longitude-$longitude)*(longitude-$longitude)+(latitude-$latitude)*(latitude-$latitude) limit 5";
		$result = $model->query($sql);
		$i = 1;
		$ret = "您身边的公益活动有:\n";
		foreach($result as $line){
			$ret .= $i . '. ' . $line['name'] . "\n";
			$i++;
		}
		return $ret;
	}


}

?>