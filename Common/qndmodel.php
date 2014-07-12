<?php

function T($name='', $tablePrefix='',$connection='') {
    static $_model  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class      =   'QnDModel';
    }
    $guid           =   $tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
    return $_model[$guid];
}

function O($name='', $tablePrefix='',$connection='') {
    T($name, $tablePrefix, $connection);
}

function extract_field($arr, $field){
    $ret = array();
    foreach($arr as $result){
        array_push($ret, $result[$field]);
    }
    return array_unique($ret);
}

class QnDModel extends Model{
    
    public function with($field, $op, $value=null){
        if($value === null){
            return $this->where(array($field=>$op));
        }
        else{
            return $this->where(array($field=>array($op, $value)));
        }
    }
    
    public function extract($field){
        $results = $this->select();
        $ret = array();
        foreach($results as $result){
            $ret[$result[$this->pk]] = $result[$field];
        }
        return $ret;
    }   
    
    public function select_as_map(){
        $results = $this->select();
        $ret = array();
        foreach($results as $result){
            $ret[$result[$this->pk]] = $result;
        }
        return $ret;
    }
    
    protected function _after_select(&$resultSet,$options) {
        if(isset($options['attach'])){
            foreach($options['attach'] as $attach){
                $this_ids = extract_field($resultSet, $attach[1]);
                $target = T($attach[0]);
                $that_map = $target->with($target->pk, 'in', $this_ids)->select_as_map();   // all the data needed in "that" table
                for($i=0;$i<count($resultSet);$i++){
                    $resultSet[$i][$attach[0]] = $that_map[$resultSet[$i][$attach[1]]];
                }
            }
        }
        
        if(isset($options['fetch'])){
            foreach($options['fetch'] as $fetch){
                for($i=0;$i<count($resultSet);$i++){
                    $target = T($fetch[0]);
                    $resultSet[$i][$fetch[0]] = $target->with($fetch[1], $resultSet[$i][$this->pk])->select();
                }
            }
        }
        
        if(isset($options['bridge'])){
            foreach($options['bridge'] as $bridge){
                // alias for all the fields in bridge
                $that_name = $bridge[0];
                $bridge_table_name = $bridge[1];
                $this_field = $bridge[2];
                $that_field = $bridge[3];
                    
                for($i=0;$i<count($resultSet);$i++){
                    $that_table = T($that_name);
                    $bridge_table = T($bridge_table_name);
                    $bridge_records = $bridge_table->with($this_field, $resultSet[$i][$this->pk])->select();
                    $bridge_results = array();
                    foreach($bridge_records as $bridge_record){
                        $that_result = $that_table->with($that_table->pk, $bridge_record[$that_field])->find();
                        $that_result['_bridge'] = $bridge_record;
                        array_push($bridge_results, $that_result);
                    }
                    $resultSet[$i][$that_name] = $bridge_results;
                }
            }
        }
        
        parent::_after_select($resultSet,$options);
    }
    
    /*
    * connect one-to-one or many-to-one information from another table
    * eg. 
    * you have: 
    * users: [id, name, email]
    * events: [id, name, user_id]
    * 
    * you use:
    * T('events')->attach('users', 'user_id')->select();
    *
    * result: [[ id, name, 'users'=>[id, name, email] ], ...]
    *
    * @param $table: the table to be attached
    * @param $with: the pk of the other table in this table.
    */
    public function attach($table, $with=null){
        if(!isset($this->options['attach'])){
            $this->options['attach'] = array();
        }
        if($with === null) $with = $table.'_id';
        array_push($this->options['attach'], array($table, $with));
        return $this;
    }
    
    /*
    * connect one-to-many information from another table
    * eg. 
    * you have: 
    * users: [id, name, email]
    * events: [id, name, user_id]
    * 
    * you use:
    * T('users')->fetch('events', 'user_id')->select();
    *
    * result: [[ id, name, email, 'events'=>[[id, name, user_id], ...] ], ...]
    *
    * @param $table: name of the other table
    * @param $with: the pk of this table in the other table.
    */
    public function fetch($table, $with=null){
        if(!isset($this->options['fetch'])){
            $this->options['fetch'] = array();
        }
        if($with === null) $with = $this->name.'_id';
        array_push($this->options['fetch'], array($table, $with));
        return $this;
    }
    
    /*
    * connect many-to-many information from another table
    * eg. 
    * you have: 
    * posts: [id, name, content]
    * tags: [id, name]
    * posts_tags: [id, posts_id, tags_id, tagger]
    * 
    * you use:
    * T('posts')->bridge('tags', 'posts_tags', 'tags_id', 'posts_id')->select();
    * -or- T('posts')->bridge('tags');
    *
    * result: [[ id, name, content, 'tags'=>[[id, name, _bridge=>[id, posts_id, tags_id, tagger]], ...] ], ...]
    *
    * @param $table: name of the other table 
    * @param $bridge_table: name of the bridge table
    * @param $with: the pk of this table.
    * @param $and_with: the pk of the other table.
    */
    public function bridge($table, $bridge_table=null, $with=null, $and_with=null){
        if(!isset($this->options['bridge'])){
            $this->options['bridge'] = array();
        }
        if($bridge_table === null) $bridge_table = $this->name.'_'.$table;
        if($with === null) $with = $this->name.'_id';
        if($and_with === null) $with = $table.'_id';
        array_push($this->options['bridge'], array($table, $bridge_table, $with, $and_with));
        return $this;
    }
}