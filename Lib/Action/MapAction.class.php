<?php
// 本类由系统自动生成，仅供测试用途
class MapAction extends Action {

    public function ajax_hotspots($key=''){
        $map_data_model = D('MapData');
        $this->ajaxReturn($map_data_model->get_all_data($key), 'JSON');
    }

    public function get_onscreen_data(){
        $map_data_model = D('MapData');

        $query_param = $_GET;
        $user_fields="id, name, longitude, latitude, type, 'users' model, province, create_time";
        $event_fields="id, name, longitude, latitude, type, 'events' model, province, create_time";
        if(!empty($_GET['model'])){
            if($_GET['model'] == 'users'){
                $query_param['user_fields'] = $user_fields;
            }
            else if($_GET['model'] == 'events'){
                $query_param['event_fields'] = $event_fields;
            }
        }
        else{
            $query_param['user_fields'] = $user_fields;
            $query_param['event_fields'] = $event_fields;
        }
        $query_param['order'] = "type='case',type='ngo',  create_time asc";
        $data = $map_data_model->query_map($query_param);
        $this->ajaxReturn($data, 'JSON');
    }

    public function get_numbers(){
        $map_data_model = D('MapData');

        $total_number = $map_data_model->query_number($_GET);
        $this->ajaxReturn(array('num'=>$total_number), 'JSON');
    }

    public function tile(){

    	$map_data_model = D('MapData');

    	$zoom = $_GET['zoom'];
    	$tilex = $_GET['x'];
    	$tiley = $_GET['y'];
        $key = $_GET['key'];
        $field = $_GET['field'];
        $type = $_GET['type'];
        $model = $_GET['model'];
        $medal = $_GET['medal'];

        $data = $map_data_model->get_tile_data($tilex, $tiley, $zoom, $_GET['field'], $_GET['key'], $_GET['type'], $_GET['model'], $_GET['medal']);

        header("content-type:image/png");  
        $img=imagecreatetruecolor(256,256);  
        //$bgcolor=ImageColorAllocate($img,0,0,0);  
        //$bgcolor = imagecolorallocatealpha($Img, 0, 0, 255, 10);
        $red=ImageColorAllocate($img,255,0,0);  
        //$bgcolortrans=ImageColorTransparent($img,$bgcolor); 
        $transparent = imagecolorallocatealpha( $img, 0, 0, 0, 127 ); 
        imagefill( $img, 0, 0, $transparent ); 
        imagealphablending($img, true); 

        $base_path = APP_PATH;

        $greenCircle = imagecreatefrompng($base_path."Public/img/markers/mini-green.png");
        imagealphablending($greenCircle, true); 
        $yellowCircle = imagecreatefrompng($base_path."Public/img/markers/mini-blue.png");
        imagealphablending($yellowCircle, true); 
        $redCircle = imagecreatefrompng($base_path."Public/img/markers/mini-red.png");
        imagealphablending($redCircle, true); 
        foreach($data as $d){
            $mc = $map_data_model->convertLL2MC($d['longitude'], $d['latitude']);
            $x = abs($mc[0] * pow(2, $zoom-18)) - (256*($tilex));
            $y = abs($mc[1] * pow(2, $zoom-18)) - (256*($tiley));
            switch ($d['type']) {
                case 'ngo':
                    imagecopy($img, $greenCircle, $x-5, 256-$y-5, 0, 0, 10, 10);
                    break;
                case 'csr':
                case 'ind':
                    imagecopy($img, $yellowCircle, $x-5, 256-$y-5, 0, 0, 10, 10);
                    break;
                case 'case':
                    imagecopy($img, $redCircle, $x-5, 256-$y-5, 0, 0, 10, 10);
                    break;
            }
            
        }
        imagesavealpha($img,true); 
        if(empty($_GET['key'])){
        Imagepng($img, "Runtime/Cache/tile-$zoom-$tilex-$tiley-$key-$field-$type-$model-$medal.png");
        }
        Imagepng($img);  
        ImageDestroy($img);  
    }
}


?>