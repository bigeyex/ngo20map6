<?php 

require('db.php');

query("insert into accounts (name, password, api_vendor, api_id, email, enabled, is_admin, image, user_id) select name, password, api_vendor, api_id, email, enabled, is_admin, image, id from users");

$accounts = query("select * from accounts");
foreach($accounts as $account){
	$account_id = $account['id'];
	$user_id = $account['user_id'];
	query("update users set account_id=$account_id where id=$user_id");
}

 ?>