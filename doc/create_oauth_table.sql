CREATE TABLE `oauth_client` (  
  `id` bigint(20) NOT NULL auto_increment,  
  `client_id` varchar(32) NOT NULL,  
  `user_id` varchar(32) NOT NULL,  
  `client_secret` varchar(32) NOT NULL,  
  `redirect_uri` varchar(200) NOT NULL,  
  `create_time` int(20) default NULL,  
  PRIMARY KEY  (`id`)  
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;  
  
CREATE TABLE `oauth_code` (  
  `id` bigint(20) NOT NULL auto_increment,  
  `client_id` varchar(32) NOT NULL,  
  `user_id` varchar(32) NOT NULL,  
  `code` varchar(40) NOT NULL,  
  `redirect_uri` varchar(200) NOT NULL,  
  `expires` int(11) NOT NULL,  
  `scope` varchar(250) default NULL,  
  PRIMARY KEY  (`id`)  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;  
  
CREATE TABLE `oauth_token` (  
  `id` bigint(20) NOT NULL auto_increment,  
  `client_id` varchar(32) NOT NULL,  
  `user_id` varchar(32) NOT NULL,  
  `access_token` varchar(40) NOT NULL,  
  `refresh_token` varchar(40) NOT NULL,  
  `expires` int(11) NOT NULL,  
  `scope` varchar(200) default NULL,  
  PRIMARY KEY  (`id`)  
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;  