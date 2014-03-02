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
    
    'LOAD_EXT_CONFIG' => 'db,content,profanity_words,credentials',
    'ADMIN_ROW_LIST' => 20,
    'PAGE_ROLLPAGE' => 10,
    'LIST_RECORD_PER_PAGE' => 20,
    'RECORD_PER_POST_WIDGET' => 5,
    'VAR_PAGE' => 'p',
    
    'OAUTH2_CODES_TABLE'=>'oauth_code',  
    'OAUTH2_CLIENTS_TABLE'=>'oauth_client',  
    'OAUTH2_TOKEN_TABLE'=>'oauth_token',  
    'OAUTH2_DB_DSN'=>'mysql://root:@localhost:3306/ngo20map'  
);
?>