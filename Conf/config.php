<?php
return array(
    //数据库配置内容
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'ngo20map',
    'DB_USER' => 'root',
    'DB_PWD' => '',
    'DB_PORT' => '3306',
    'DB_PREFIX' => '',
    "LOAD_EXT_FILE"=>"htmlhelpers",
    'URL_MODEL' => 2,
    'APP_DEBUG' => true,
        
    'LOG_RECORD' => true, // 开启日志记录
    'SESSION_AUTO_START' => true, //是否开启session
    
    'URL_CASE_INSENSITIVE' =>false,//URL不区分大小写
    
    'TMPL_PARSE_STRING'  =>array(
         '__PUBLIC__' => '/Common', 
         '__JS__' => '/Public/js/', // 增加新的JS类库路径替换规则
         '__IMG__' => '/Public/img/',
         '__CSS__' => '/Public/css/',
         '__UPLOAD__' => '/Uploads', // 增加新的上传路径替换规则
    ),
    
    'ORG_FIELDS' => array('健康扶助','妇女儿童','教育助学','环境保护','社区发展','灾害管理',
            '劳工福利','社会企业','HIV&AIDS','文化保护','性权利保护','政策倡导','信息网络','支持型NGO'),
);
?>