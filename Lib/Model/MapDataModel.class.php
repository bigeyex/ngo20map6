<?php

class MapDataModel extends Model{

	protected $autoCheckFields = false;

    public function get_all_data($key = ''){
        $model = new Model();
        if($key == ''){
            $sql = "(select id, name, longitude, latitude, type, 'users' model, province, work_field rec_field, create_time from users where type != 'ind' and is_checked=1) union (select id, name, longitude, latitude, type, 'events' model, province, item_field rec_field, create_time from events where is_checked=1) order by type!='case',type!='ngo',create_time desc";
        }
        else{
            $sql = "(select id, name, longitude, latitude, type, 'users' model, province, work_field rec_field, create_time from users where type != 'ind' and (name like '%$key%' or introduction like '%$key%') and is_checked=1) union (select id, name, longitude, latitude, type, 'events' model, province, item_field rec_field, create_time from events where (name like '%$key%' or description like '%$key%') and is_checked=1) order by type!='case',type!='ngo',create_time desc";            
        }
        return $model->query($sql);
    }

    public function get_ngo_network_data($ngo_id){
        $user_model = new UsersModel();
        $event_model = new EventsModel();
        $ngo_id = intval($ngo_id);  //defense against exploit

        //select the events of the user
        $events_of_user = $event_model->field('id,longitude,latitude')
            ->where(array('user_id'=>$ngo_id))->select();
        $user = $user_model->find($ngo_id);

        //select the related ngo of the user
        $fields = explode(',', $user['work_field']);
        $sql = "select id,longitude,latitude, 0";
        foreach($fields as $field){
            $sql .= "-10*if(work_field like '%$field%',1,0)";
        }
        $sql .= " score from users where type='ngo' and is_checked=1 order by score limit 5";
        $ngo_of_user = $this->query($sql);

        //select the related csr with the same work field with the user
        $sql = "select id,longitude,latitude, 0";
        foreach($fields as $field){
            $sql .= "-10*if(item_field like '%$field%',1,0)";
        }
        $sql .= " score from events where type='csr' and is_checked=1 order by score limit 5";
        $csr_of_user = $this->query($sql);

        return array(
            'events' => $events_of_user,
            'related_user' => $ngo_of_user,
            'related_csr' => $csr_of_user
            );

    }

    public function invalidate_tile($lon, $lat){
        $lon = floatval($lon);
        $lat = floatval($lat);
        $point = $this->convertLL2MC($lon, $lat);
        $lon = $point[0];
        $lat = $point[1];
        for($z=1;$z<=18;$z++){
            $tile_x = $lon * pow(2, $z-18) / 256;
            $tile_y = $lat * pow(2, $z-18) / 256;
            $this->unlink_tile_file($z, $tile_x, $tile_y);
        }
    }

	public function get_tile_data($tilex, $tiley, $zoom, $field, $key='', $type='', $model='', $medal='', $province=''){
		$scalex = array(0,301.421310,150.710655,75.355327,37.677664,18.838832,9.419416,4.709708,2.354854,1.177427,0.588714,0.294357,0.147179,0.07359,0.036795,0.018397,0.009199,0.0046,0.0023,0.001149);
        $scaley = array(0,138.558225,88.011798,50.105148,26.953469,13.990668,7.125178,3.594854,1.805441,0.904715,0.452855,0.226552,0.113307,0.056661,0.028332,0.014166,0.007084,0.003542,0.001771,0.000885);
        
        $icon_size = 10;

        //caculate range
        $minlon = (256 * $tilex - $icon_size) / pow(2, $zoom-18);
        $maxlon = (256 * ($tilex+1) + $icon_size) / pow(2, $zoom-18);
        $minlat = (256 * $tiley - $icon_size) / pow(2, $zoom-18);
        $maxlat = (256 * ($tiley+1) + $icon_size) / pow(2, $zoom-18);
        $minrange = $this->convertMC2LL($minlon, $minlat);
        $maxrange = $this->convertMC2LL($maxlon, $maxlat);
        $minlon = $minrange[0];$minlat = $minrange[1];
        $maxlon = $maxrange[0];$maxlat = $maxrange[1];

        $cond = "longitude>$minlon and longitude<$maxlon and latitude>$minlat and latitude<$maxlat";
        $user_fields="id, longitude, latitude, type, 'users' model, create_time";
        $event_fields="id, longitude, latitude, type, 'events' model, create_time";
        if(!empty($model)){
            if($model == 'users'){
                $event_fields = '';
            }
            else if($model == 'events'){
                $user_fields = '';
            }
        }
        if($type=='ngo')$type='exngo';
        if($type=='csr')$type='excsr';
        return $this->query_map(array(
            'user_fields' => $user_fields,
            'event_fields' => $event_fields,
            'type' => $type,
            'field' => $field,
            'province' => $province,
            'key' => $key,
            'medal' => $medal,
            'where' => $cond,
            'order' => "type='case',type='ngo',  create_time asc",
            ));
        
        
	}


	/* 
		usage: 
		query_map(array(
			'users_fields' => '*',
			'events_fields' => '*', 
			'type' => 'ngo',
			'field' => '教育助学',
			'province' => '安徽',
            'start_lon' => 58.992792,
            'end_lon' => 152.083114
			'progress' => '',
            'res_tags' => '捐赠资源',
            'where' => $cond,
            'order' => "type='case',type='ngo',  create_time asc",
		));

	*/
	public function query_map($param){
        $users_where = array();
        $events_where = array();
        // $param = san($param);

        if(!empty($param['where'])){
            $users_where[] = $events_where[] = $param['where'];
        }

        $users_where[] = "type != 'ind'";
        if(!empty($param['type'])){
            if($param['type'] == 'excsr' || $param['type'] == 'csr'){
                //special case #1: ind events belongs to csr
                $users_where[] = "type='csr'";
                $events_where[] = "(type='csr' or type='ind')";
            }
            else if($param['type'] == 'csr'){
                $users_where[] = $events_where[] = "type='csr'";
            }
            else if($param['type'] == 'exngo' || $param['type'] == 'ngo'){
                //special case #2: fund belongs to extended ngo concept
                $users_where[] = $events_where[] = "(type='ngo' or type='fund')";
            }
            else{
                $users_where[] = $events_where[] = "type='".$param['type']."'";
            }
        }

        //领域限制
        if(!empty($param['field'])){
                $events_where[] = "item_field like '%". $param['field'] ."%'";
                $users_where[] = "work_field like '%". $param['field'] ."%'";
        }

        //省份限制
        if(!empty($param['province'])){
                $users_where[] = $events_where[] = "province like '%". $param['province'] ."%'";
        }

        if(!empty($param['medal_name'])){
            $users_where[] = "medals like '%".x($param['medal_name'])."%'";
        }

        if(!empty($param['medal'])){
            $medal_model = new MedalModel();
            $user_ids = $medal_model->select_user_ids_by_medal_id($param['medal']);
            $events_where[] = "user_id in (" . implode(',', $user_ids) . ")";
            $users_where[] = "id in (" . implode(',', $user_ids) . ")";
        }

        //资源标签限制
        if(!empty($param['res_tags'])){
            $events_where[] = "res_tags like '%". $param['res_tag'] ."%'";
        }

        if(!empty($param['res_tags2'])){
            $events_where[] = "res_tags like '%". $param['res_tag2'] ."%'";
        }

        if(!empty($param['key'])){
                $events_where[] = "(name like '%". $param['key'] ."%' or description like '%". $param['key'] ."%')";
                $users_where[] = "(name like '%". $param['key'] ."%' or introduction like '%". $param['key'] ."%')";
        }

        if(!empty($param['start_lon'])){
            $events_where[] = "longitude > " . $param['start_lon'];
            $users_where[] = "longitude > " . $param['start_lon'];
        }

        if(!empty($param['end_lon'])){
            $events_where[] = "longitude < " . $param['end_lon'];
            $users_where[] = "longitude < " . $param['end_lon'];
        }

        if(!empty($param['start_lat'])){
            $events_where[] = "latitude > " . $param['start_lat'];
            $users_where[] = "latitude > " . $param['start_lat'];
        }

        if(!empty($param['end_lat'])){
            $events_where[] = "latitude < " . $param['end_lat'];
            $users_where[] = "latitude < " . $param['end_lat'];
        }
        
        //进度限制
        if(!empty($param['progress'])){
            $today = date('Y-m-d');
            switch($param['progress']){
                case 'planning':
                    $events_where[] = "events.begin_time>'$today'";
                    break;
                case 'running':
                    $events_where[] = "events.begin_time<'$today' and events.end_time>'$today'";
                    break;
                case 'finished':
                    $events_where[] = "events.end_time<'$today'";
                    break;
                case 'delayed':
                    $events_where[] = "events.progress=1";
                    break;
                case 'failed':  //尚未考虑拖延一定时间自动失败
                    $events_where[] = "events.progress=2";
                    break;
                case 'daily':
                    $events_where[] = "events.progress=3";
                    break;
            }
        }
        
        // if it is not expert mode, only show checked items
        if(!isset($param['expert_mode'])){
            $events_where[] = $users_where[] = 'is_checked=1';
        }
        $events_where[] = $users_where[] = 'enabled=1';
        
        $order = "";
        if(!empty($param['order'])){
            $order = ' order by '.$param['order'];
        }

        if(isset($param['expert_mode'])){
            $order = ' order by is_checked, create_time desc';
        }

        $limit = "";
        if(!empty($param['limit'])){
            $limit = ' limit '.$param['limit'];
        }

        $sql_list = array();
        if(!empty($param['user_fields'])){
            $sql_list[] = '(select '.$param['user_fields'].' from users where '.implode(" and ", $users_where).')';
        }
        if(!empty($param['event_fields'])){
            $sql_list[] = '(select '.$param['event_fields'].' from events where '.implode(" and ", $events_where).')';
        }
        $sql = implode(' union ', $sql_list) . $order . $limit; 
        
        $result = $this->query($sql);
        if(!$result) $result = array();
        return $result;
    }

    public function query_number($query_param=array()){
        $user_fields="count(*) cnt";
        $event_fields="count(*) cnt";
        $query_param = x($query_param);
        if(!empty($query_param['model'])){
            if($query_param['model'] == 'users'){
                $query_param['user_fields'] = $user_fields;
            }
            else if($query_param['model'] == 'events'){
                $query_param['event_fields'] = $event_fields;
            }
        }
        else{
            $query_param['user_fields'] = $user_fields;
            $query_param['event_fields'] = $event_fields;
        }
        $data = $this->query_map($query_param);
        $total_number = 0;
        foreach($data as $d){
            $total_number += $d['cnt'];
        }
        return $total_number;
    }


	//tackle with map coordinates
	public function convertLL2MC($lng, $lat) {
	    $LLBAND = array(75, 60, 45, 30, 15, 0);
	    $LL2MC = array(array( -0.0015702102444, 111320.7020616939, 1704480524535203, -10338987376042340, 26112667856603880, -35149669176653700, 26595700718403920, -10725012454188240, 1800819912950474, 82.5), array(0.0008277824516172526, 111320.7020463578, 647795574.6671607, -4082003173.641316, 10774905663.51142, -15171875531.51559, 12053065338.62167, -5124939663.577472, 913311935.9512032, 67.5), array(0.00337398766765, 111320.7020202162, 4481351.045890365, -23393751.19931662, 79682215.47186455, -115964993.2797253, 97236711.15602145, -43661946.33752821, 8477230.501135234, 52.5), array(0.00220636496208, 111320.7020209128, 51751.86112841131, 3796837.749470245, 992013.7397791013, -1221952.21711287, 1340652.697009075, -620943.6990984312, 144416.9293806241, 37.5), array( -0.0003441963504368392, 111320.7020576856, 278.2353980772752, 2485758.690035394, 6070.750963243378, 54821.18345352118, 9540.606633304236, -2710.55326746645, 1405.483844121726, 22.5), array( -0.0003218135878613132, 111320.7020701615, 0.00369383431289, 823725.6402795718, 0.46104986909093, 2351.343141331292, 1.58060784298199, 8.77738589078284, 0.37238884252424, 7.45));

	    $T = array('lng'=>$lng, 'lat'=>$lat);
	    $T['lng'] = $this->getLoop($T['lng'], -180, 180);
	    $T['lat'] = $this->getRange($T['lat'], -74, 74);
	    for ($cF = 0; $cF < count($LLBAND); $cF++) {
	        if ($T['lat'] >= $LLBAND[$cF]) {
	            $cG = $LL2MC[$cF];
	            break;
	        }
	    }
	    if (!$cG) {
	        for ($cF = count($LLBAND) - 1; $cF >= 0; $cF--) {
	            if ($T['lng'] <= - $LLBAND[$cF]) {
	                $cG = $LL2MC[$cF];
	                break;
	            }
	        }
	    }
	    $cH = $this->convertor($T, $cG);
	    $T = array(round($cH['lng'], 2), round($cH['lat'], 2));
	    return $T;
	}
	public function convertMC2LL($lng, $lat){
	    $MCBAND = array(12890594.86, 8362377.87, 5591021, 3481989.83, 1678043.12, 0);
	    $MC2LL =array(array(1.410526172116255e-8, 0.00000898305509648872, -1.9939833816331, 200.9824383106796, -187.2403703815547, 91.6087516669843, -23.38765649603339, 2.57121317296198, -0.03801003308653, 17337981.2), array( -7.435856389565537e-9, 0.000008983055097726239, -0.78625201886289, 96.32687599759846, -1.85204757529826, -59.36935905485877, 47.40033549296737, -16.50741931063887, 2.28786674699375, 10260144.86), array( -3.030883460898826e-8, 0.00000898305509983578, 0.30071316287616, 59.74293618442277, 7.357984074871, -25.38371002664745, 13.45380521110908, -3.29883767235584, 0.32710905363475, 6856817.37), array( -1.981981304930552e-8, 0.000008983055099779535, 0.03278182852591, 40.31678527705744, 0.65659298677277, -4.44255534477492, 0.85341911805263, 0.12923347998204, -0.04625736007561, 4482777.06), array(3.09191371068437e-9, 0.000008983055096812155, 0.00006995724062, 23.10934304144901, -0.00023663490511, -0.6321817810242, -0.00663494467273, 0.03430082397953, -0.00466043876332, 2555164.4), array(2.890871144776878e-9, 0.000008983055095805407, -3.068298e-8, 7.47137025468032, -0.00000353937994, -0.02145144861037, -0.00001234426596, 0.00010322952773, -0.00000323890364, 826088.5));

	    $cE = array('lng'=>$lng, 'lat'=>$lat);
	    $cF = array('lng'=>abs($lng), 'lat'=>abs($lat));
	    for($cG=0; $cG<count($MCBAND); $cG++){
	        if($cF['lat'] >= $MCBAND[$cG]){
	            $cH = $MC2LL[$cG];
	            break;
	        }
	    }
	    $T = $this->convertor($cE, $cH);
	    $cE = array(round($T['lng'], 6), round($T['lat'], 6));
	    return $cE;
	}
	function convertor($cF, $cG) {
	    if (!$cF || !$cG) {
	        return false;
	    }
	    $T = $cG[0] + $cG[1] * abs($cF['lng']);
	    $cE = abs($cF['lat']) / $cG[9];
	    $cH = $cG[2] + $cG[3] * $cE + $cG[4] * $cE * $cE + $cG[5] * $cE * $cE * $cE + $cG[6] * $cE * $cE * $cE * $cE + $cG[7] * $cE * $cE * $cE * $cE * $cE + $cG[8] * $cE * $cE * $cE * $cE * $cE * $cE;
	    $T *= ($cF['lng'] < 0 ? -1: 1);
	    $cH *= ($cF['lat'] < 0 ? -1: 1);
	    return array("lng" => $T, "lat" => $cH);
	}

	function getRange($cF, $cE, $T)
    {
        if ($cE != null) {
            $cF = max($cF, $cE);
        }
        if ($T != null) {
            $cF = min($cF, $T);
        }
        return $cF;
    }
    function getLoop($cF, $cE, $T) {
        while ($cF > $T) {
            $cF -= $T - $cE;
        }
        while ($cF < $cE) {
            $cF += $T - $cE;
        }
        return $cF;
    }

    function unlink_tile_file($z, $x, $y){
        $x = intval($x);
        $y = intval($y);
        $z = intval($z);
        $base_path = 'Runtime/Cache';
        $list = exec("ls -1 $base_path/tile-$z-$x-$y-*", $output, $error);
        foreach ($output as &$file){
            unlink($file);
        }

    }

}

?>