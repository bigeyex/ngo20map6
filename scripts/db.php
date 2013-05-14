<?php
define ("USER","root");
define ("PASSWORD","");

function query($sql){
	// 1. connect to db
	$con = mysql_connect("localhost", USER, PASSWORD);
	if (!$con) {
		die ("cannot connect to database!");
	}

	// 2. select db
	mysql_select_db("ngo20map", $con);

	// 3. start query 
	$result = mysql_query($sql);
	if (!$result){
		die (mysql_error());
	}
	$result_array = array();


	// 4. print out data
	if ($result !== true){
		while ($row = mysql_fetch_array($result)){
			$result_array[] = $row;
		}
	}
	return $result_array;


	// 5. close db
	mysql_close($con);


}

query("set names utf8");
?>

