<?php

class UsersModel extends Model{
	function completeness($user=null){
        $user = $_SESSION['login_user'];
        $check_fields = array('aim', 'work_field', 'register_year', 'service_area', 'staff_fulltime', 'staff_parttime', 'staff_volunteer', 'website', 'public_email', 'phone', 'province', 'city', 'county', 'place', 'contact_name', 'weibo');
        $completeness = 10;
        $point_per_item = 60/count($check_fields);
        foreach ($check_fields as $field) {
            if(!empty($user[$field])){
                $completeness += $point_per_item;
            }
        }
        $model = new Model();
        $event_count_result = $model->query('select count(*) cnt from events where user_id='.$user['id']);
        if($event_count_result[0]['cnt']>0){
            $completeness+=20;
        }
        $image_count_result = $model->query('select count(*) cnt from media where event_id in (select id from events where user_id='.$user['id'].')');
        if($image_count_result[0]['cnt']>0){
            $completeness+=10;
        }
        return round($completeness);
    }
    
}

?>