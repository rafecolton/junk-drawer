<?php
require_once("functions.php");
restrict_access_to_AJAX();

$type =  f3::get('PARAMS[type]');

if ($type == "manual"):

	foreach ($_POST as $key => $value):
		$_POST[$key] = mysql_real_escape_string($value);
	endforeach;

	$department = trim($_POST["department"]);
	$course_no = trim($_POST["courseNo"]);
	$name = trim($_POST["name"]);
	$description = $_POST["description"];
	$no_credits = $_POST["noCredits"];
	$grade_option = $_POST["gradeOption"];
	$pstring = $_POST["prerequisites"];
	$cstring = $_POST["corequisites"];
		
	DB::sql("REPLACE INTO Courses VALUES ('$course_no', '$department', '$description','$name','$grade_option','$no_credits');");
	
	echo "Course successfully added.<br>";
	
	if (isset($pstring) && $pstring != ""):
		$parray = preg_split("/,/",$pstring);
		foreach ($parray as $prereq):
			$split = preg_split("/ /",trim($prereq));
			$split[0] = trim(strtoupper($split[0]));
			$split[1] = trim($split[1]);
			$p_department = $split[0];
			$p_course_no = $split[1];
			
			DB::sql("REPLACE INTO Prerequisites VALUES ('$course_no','$department','$p_course_no','$p_department');");
			
		endforeach;
		echo "Prerequisites successfully added.<br>";
		
	endif;
	
	if (isset($cstring) && $cstring != ""):
		$carray = preg_split("/\n/",$cstring);
		foreach ($carray as $coreq):
			$split = preg_split("/ /",$coreq);
			$split[0] = trim(strtoupper($split[0]));
			$split[1] = trim($split[1]);
			$c_department = $split[0];
			$c_course_no = $split[1];
			
			DB::sql("REPLACE INTO Corequisites VALUES ('$course_no','$department','$c_course_no','$c_department');");
			
		endforeach;
		echo "Corequisites successfully added.<br>";
		
	endif;
	
	
else:
	return;
endif;


?>