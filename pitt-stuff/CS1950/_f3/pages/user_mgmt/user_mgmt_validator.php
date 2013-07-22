<?php

$username = $_POST["username"];
$password = hash("sha512",$_POST["password"]);

DB::sql("select * from all_users where username='$username' and password='$password';");

$results = F3::get('DB->result');

if (count($results) == 0):
	echo "Invalid User";
else:
	$result = $results[0];
	$_SESSION["logged_in"] = true;
	$_SESSION["username"] = $result["Username"];
	$_SESSION["usertype"] = $result["User_Type"];
	echo "true";
endif;

?>