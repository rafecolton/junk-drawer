<?php
require_once("functions.php");
load_base();
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Control Panel</title>
<?php print_links(); ?>

</head>
<body>

<?php
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == false):
	lock('cpanel_content/unlock/'); 
else:
	include("control_panel_content.php");
	unlock();
endif;

?>

</body>
</html>