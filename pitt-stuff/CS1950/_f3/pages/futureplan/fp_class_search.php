<?php

/****************
	TODOs:
	- load grade from enrollment table
	
****************/

require_once("functions.php");
restrict_access_to_AJAX();

//This section is for loading the number and department of all courses for the autocomplete list
$course =  F3::get('PARAMS[course]');

if (isset($course) && $course == "load_list"):
	
	DB::sql("SELECT Department, Course_No from Courses;");
	
	$results = F3::get('DB->result');
	
	$returnVal = "";
	
	foreach ($results as $item):
		while (strlen($item["Course_No"]) < 4):
			$item["Course_No"] = "0".$item["Course_No"];
		endwhile;
		$returnVal .= $item["Department"]." ".$item["Course_No"].",";
	endforeach;
	
	$returnVal = rtrim($returnVal,",");
	
	echo $returnVal;

//This section is for searching for individual courses
else:
	$courseID = F3::get('PARAMS[courseID]');
	$uniqueID = F3::get('PARAMS[uniqueID]');
	
	$id_array = preg_split("/ /",$courseID);
	
	if (count($id_array) != 2){
		unset($course);
		$course["uniqueID"] = $uniqueID;
		$course["courseID"] = $courseID;
		$course["found"] = "false";
		$course["department"] = "n/a";
		$course["courseNo"] = "n/a";
		$course["courseString"] = "n/a";
		$course["name"] = $courseID;
		$course["description"] = "n/a";
		$course["noCredits"] = "n/a";
		$course["gradeOption"] = "n/a";
		$course["prerequisites"] = array("n/a");
		$course["corequisites"] = array("n/a");
		$course["countsAs"] = $courseID;
		$course["previousCountsAs"] = $courseID;
		echo json_encode($course);
		return;	
	}	
	$department = $id_array[0];
	$course_no = $id_array[1];
	
	DB::sql("SELECT * FROM Courses WHERE Department = '$department' AND Course_No = '$course_no';");
	
	$results = F3::get('DB->result');
	if (count($results) == 0):
		unset($course);
		$course["uniqueID"] = $uniqueID;
		$course["courseID"] = $courseID;
		$course["found"] = "false";
		$course["department"] = "n/a";
		$course["courseNo"] = "n/a";
		$course["courseString"] = "n/a";
		$course["name"] = $courseID;
		$course["description"] = "n/a";
		$course["noCredits"] = "n/a";
		$course["gradeOption"] = "n/a";
		$course["prerequisites"] = array("n/a");
		$course["corequisites"] = array("n/a");
		$course["countsAs"] = $courseID;
		$course["previousCountsAs"] = $courseID;
		echo json_encode($course);
		return;
	endif;
	
	$result = $results[0];
	
	unset($course);
	
	/*
	this.prerequisites = new Array();
	this.corequisites = new Array();
	*/
	
	$course["department"] = $department;
	$course["courseNo"] = $course_no;
	while (strlen($course["courseNo"]) < 4):
			$course["courseNo"] = "0".$course["courseNo"];
			$course_no = $course["courseNo"];
	endwhile;
	$course["uniqueID"] = $uniqueID;
	$course["courseID"] = $courseID;
	$course["courseString"] = $department." ".$course_no;
	$course["name"] = $result["Name"];
	$course["description"] = $result["Description"];
	$course["noCredits"] = $result["No_Credits"];
	$course["gradeOption"] = $result["Grade_Option"];
	$course["prerequisites"] = array();
	$course["corequisites"] = array();
	$course["found"] = "true";
	$course["countsAs"] = $courseID;
	$course["previousCountsAs"] = $courseID;
	
	DB::sql("SELECT P_Department, P_Course_No FROM Prerequisites WHERE Course_No = '$course_no' AND Department = '$department';");
	$results = F3::get('DB->result');
	
	if (count($results) != 0):
		foreach ($results as $result):
			while (strlen($result["P_Course_No"]) < 4):
				$result["P_Course_No"] = "0".$result["P_Course_No"];
			endwhile;
			$course["prerequisites"][] = $result["P_Department"]." ".$result["P_Course_No"];
		endforeach;
		
		else:
			$course["prerequisites"] = "n/a";
	endif;
	
	DB::sql("SELECT C_Department, C_Course_No FROM Corequisites WHERE Course_No = '$course_no' AND Department = '$department';");
	$results = F3::get('DB->result');
		
	if (count($results) != 0):
	foreach ($results as $result):
			while (strlen($result["C_Course_No"]) < 4):
				$result["C_Course_No"] = "0".$result["C_Course_No"];
			endwhile;
			$course["corequisites"][] = $result["C_Department"]." ".$result["C_Course_No"];
		endforeach;
	else:
		$course["corequisites"] = "n/a";
	endif;
	
	echo json_encode($course);
	//echo $courseID;
	//return;
endif;


?>