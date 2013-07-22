<?php

if ($_POST["usertype"] == 'admin'):

	$username = $_POST["username"];
	$password = $_POST["password"];
	$usertype = $_POST["usertype"];
	$email = $_POST["email"];

	$password = hash("sha512",$password);

	echo DB::sql("insert into all_users values ('$username','$password','$usertype');");
	
	switch($usertype):
	
		case "admin":
			DB::sql("insert into admin_users values('$username','$password','$email');");
			break;
		
		case "student":
			
			break;
			
		case "advisor":
		
			break;
			
		case "demo":
			
			break;
	endswitch;

else:
	echo "error";

endif;


?>