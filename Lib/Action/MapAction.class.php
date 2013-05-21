<?php
// 本类由系统自动生成，仅供测试用途
class MapAction extends Action {

    public function ajax_hotspots(){
        $map_data_model = D('MapData');
        $this->ajaxReturn($map_data_model->get_all_data(), 'JSON');
    }

    public function tile(){

    	$map_data_model = D('MapData');

    	$zoom = $_GET['zoom'];
    	$tilex = $_GET['x'];
    	$tiley = $_GET['y'];

        $data = $map_data_model->get_tile_data($tilex, $tiley, $zoom, $_GET['field'], $_GET['province'], $_GET['type']);
        
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
        //Imagepng($img, "Runtime/Cache/tile-$zoom-$tilex-$tiley.png");
        }
        Imagepng($img);  
        ImageDestroy($img);  
    }
}


?>