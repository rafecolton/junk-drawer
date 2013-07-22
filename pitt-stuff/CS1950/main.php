<?php
require_once("functions.php");
load_base();
?>

<!doctype html>
<html>
<head>
<title>Test Page</title>
<?php print_links(); ?>
</head>
<body>
<button onclick="doit()">Do It</button>

<script>
function doit(){
/*
	get("about/","alert");
	post("about/","alert");
	put("about/","alert");
	del("about/","alert");
*/
alert("There is no content here yet.");
}
</script>
</body>
</html>