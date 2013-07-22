<?php
require_once("functions.php");
load_base();
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title>Pitt: Plan for the Future</title>
<?php print_links() ?>
</head>
<body>
<?php
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false):
	lock('fplan_unlock/'); 
else:
	include("fp_unlocked.php");
endif;

?>
</body>
</html>