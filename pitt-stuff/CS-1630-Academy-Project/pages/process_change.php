<?
	require("../glue.php");
	init("form_process");

	$user_id = $_SESSION["user_id"];

	$results = $db->arrayQuery("select password, salt from user where user_id='$user_id'");

	if (empty($results))
	{
		echo "Invalid User";
		die;
	}

	$old = sqlite_escape_string($_POST["old_password"]);
	$new = sqlite_escape_string($_POST["new_password"]);
	$new2 = sqlite_escape_string($_POST["new_password_2"]);

	if (!old_password_match($old, $results[0]))
	{
		echo "Old Password Must Match";
		die;
	}

	if ($new != $new2)
	{
		echo "New Passwords Must Match";
		die;
	}

	$new_salt = make_salt();

	$pass = crypt($new, '$5$'.$new_salt);
	$query = "update User set password = '$pass', salt = '$new_salt' where user_id = '$user_id'";	

	@$result = $db->queryExec($query, $error);
	if (empty($result) || $error)
	{
		echo "Error Updating Password";
		die;
	}
	else
	{
		echo "Success";
		die;
	}

	function old_password_match($old, $info)
	{
		return crypt($old, '$5$'.$info["salt"]) == $info["password"];
	}

	function make_salt()
	{
		$characters = "abcdefghijklmnopqrstuvwxyz0123456789";
		$length = strlen($characters);
		$salt = "";

		for ($x=0; $x<16; $x++)
		{

			$salt .= $characters[rand(0,$length-1)];
		}

		return $salt;
	}