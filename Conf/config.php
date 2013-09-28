<?php
return array(
    "LOAD_EXT_FILE"=>"htmlhelpers,auth",
    'URL_MODEL' => 2,
    'APP_DEBUG' => true,
        
    'LOG_RECORD' => true, // 开启日志记录
    'SESSION_AUTO_START' => true, 
    
    'URL_CASE_INSENSITIVE' =>false,
    
    'TMPL_PARSE_STRING'  =>array(
         '__PUBLIC__' => '/Common', 
         '__JS__' => '/Public/js/', 
         '__IMG__' => '/Public/img/',
         '__CSS__' => '/Public/css/',
         '__UPLOAD__' => '/Uploads', 
    ),
    
    'LOAD_EXT_CONFIG' => 'db,content,profanity_words',
    'ADMIN_ROW_LIST' => 20,
    'PAGE_ROLLPAGE' => 10,
    'VAR_PAGE' => 'p',
);
?>