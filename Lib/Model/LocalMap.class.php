<?php

class LocalMap{
    public $id;
    public $name;
    public $description;
    public $admin_id;
    public $admin_user;
    public $config;
    
    public function __construct($db_record){
        $this->id = $db_record->id;
        $this->name = $db_record->name;
        $this->description = $db_record->description;
        $this->admin_id = $db_record->admin_id;
        $this->config = json_decode($db_record->config);
        
        // fetch admin_user from user table
        $user_model = M('Users');
        $this->admin_user = $user_model->find($admin_id);
    }
    
    public function get_content($key){
        $local_content_model = M('LocalContent');
        $result = $local_content_model->where(array(
            'local_id' => $this->id,
            'local_key' => $key,
        ))->select();
        
        if(count($result) == 0) return false;
        if(count($result) == 1){
            $content = $result[0]['local_content'];
            $content['id'] = $result[0]['id'];
            $content['create_time'] = $result[0]['create_time'];
            $content['update_time'] = $result[0]['update_time'];
            return $content;
        }
        if(count($result) > 1){
            return $result;
        }
    }
    
    public function insert_content($key, $value){
    
    }
    
    public function set_content($key, $value){
        
    }

}