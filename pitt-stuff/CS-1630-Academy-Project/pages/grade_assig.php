<?
	require("../glue.php");
	init("page");
	enqueue_script("jquery.flexipage.min.js");
	get_header();
	 
	if($_SESSION["usertype"] != "teacher" && $_SESSION["usertype"] != "admin")
	{
		error_message("Invalid permissions...");
		get_footer();
		die;
	}

	if (isset($_GET["class_id"]) && isset($_GET["assignment_id"]))
	{
		$class_id = sqlite_escape_string($_GET['class_id']);
		$assignment_id = sqlite_escape_string($_GET['assignment_id']);
	}
	else
	{
		echo "<h1>Grade Assignment</h1>";
		echo "<h2>Select Course & Assignment to Grade</h2>";
		$user_id = $_SESSION["user_id"];
		$results = $db->arrayQuery("select class_id from enrollment where user_id='$user_id'");
		echo "Course: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <select id='course_id' name='course_id' class='drop-down'>\n";
		echo "<option value=''>--</option>\n";
		foreach ($results as $course)
		{
			$id = $course["class_id"];
			$courseinfo = $db->arrayQuery("select * from class where class_id = '$id'");
			if (!empty($courseinfo))
			{
				$name = $courseinfo[0]["class_name"];
				echo "<option value='$id'>$name</option>\n";
			}
		}
		echo "</select><br>\n";
		echo "Assignment: &nbsp;&nbsp;&nbsp; <select id='assignment_id' name='assignment_id' class='drop-down'>\n";
		echo "<option value=''>--</option>\n";
		echo "</select><br>\n";
		echo "<button name='select' id='select' disabled='disabled' onclick='goToGradingPage()'>Select Assignment</button>"
		?>
		<script>
			$(document).ready(function(){
				$("#course_id").change(function(){
					var course_id = $(this).val();
					if (course_id == ""){
						$('#select').attr("disabled","disabled");
						$('#assignment_id').find('option').remove().end().append("<option value=''>--</option>\n");
					}
					else{
						$data = "token=<?= $_SESSION['public_token'] ?>&course_id=" + course_id;
						post("get_assig_list.php",$data, function(mylist){
							if (mylist.indexOf("error") != -1){
								alert("Unable to retrieve courses. Please try again.");
							}
							else{
								$('#assignment_id').find('option').remove().end().append("<option value=''>--</option>\n");
								var list = JSON.parse(mylist);
								for (x in list){
									var title = list[x].title;
									var id = list[x].assignment_id;
									$("#assignment_id").append("<option value='" + id + "'>" + title + "</option>");
								}
							}
						});
					}
				});
				$('#assignment_id').change(function(){
					var id = $(this).val();
					if (id != ""){
						$('#select').removeAttr("disabled");
					}
					else{
						$("#select").attr("disabled","disabled");
					}
				});
			});
			function goToGradingPage(){
				window.location = "<?= HOME_DIR ?>pages/grade_assig.php?class_id=" + $("#course_id").val() + "&assignment_id=" + $('#assignment_id').val();
			}
		</script>
		<?
		get_footer();
		die;
	}

	$results = $db->arrayQuery("select * from class where class_id='$class_id'");
	if (empty($results))
	{
		error_message("Invalid course selection...");
		get_footer();
		die;
	}
	$course = $results[0];
	$course_title = $course["class_name"];
	$class_path = preg_replace("/\ /","_",$course_title)."-".$course["class_id"];

	$results = $db->arrayQuery("select * from assignment where assignment_id='$assignment_id'");
	if (empty($results))
	{
		error_message("Invalid assignment selection...");
		get_footer();
		die;
	}
	$assignment = $results[0];
	$assignment_title = $assignment['title'];
	$assig_path = preg_replace("/\ /", '_', $assignment_title)."-".$assignment["assignment_id"];	

	echo "<h1>Grading $assignment_title</h1>";
	echo "<div class='message-wrapper' id='success-message' style='display: none;'><div class='info message'>Grade Successfully Submitted</div></div>";
	echo "<div class='message-wrapper' id='failure-message' style='display: none;'><div class='warning message'>Error Submitting Grade</div></div>";
	echo "<div class='message-wrapper' id='caution-message' style='display: none;'><div class='caution message'>Grade May Not Have Been Submitted</div></div>";


	if(!is_dir(BASE_PATH.$class_path))
	{
		error_message("Folder for class not found.");
		get_footer();
		die;
	}
	if (!is_dir(BASE_PATH.$class_path."/".$assig_path))
	{
		error_message("Folder for assignment not found.");
		get_footer();
		die;	
	}


	$full_assig_path = BASE_PATH.$class_path."/".$assig_path;



	$folder_list = scandir($full_assig_path);
	array_shift($folder_list);
	array_shift($folder_list);

	foreach ($folder_list as $key => $value)
	{
		if (!is_dir($full_assig_path."/".$value))
		{
			unset($folder_list[$key]);
		}
	}


	if (empty($folder_list))
	{
		error_message("No student submissions available.");
		get_footer();
		die;
	}

	echo "<ul id='student-files'>";
	foreach ($folder_list as $student_folder)
	{
		echo "<li><div class='grade-sheet'>";

		$tokens = preg_split("/-/",$student_folder);
		$student_name = preg_replace("/_/", " ", $tokens[0]);
		$user_id = $tokens[1];

		echo "<h2>$student_name</h2>";

		$student_path = $full_assig_path."/".$student_folder;
		$file_list = scandir($student_path);
		array_shift($file_list);
		array_shift($file_list);

		echo "Select a file:<br>";
		echo "<select id='$user_id'>\n";
		echo "<option value='none'>--</option>\n";
		
		$count = 0;
		foreach ($file_list as $file)
		{
			if (substr_compare($file, ".class", -strlen(".class"), strlen(".class"), true) == 0): continue; endif;
			echo "<option value='$file' id='file$count-$user_id'>$file</option>\n";
			$count++;
		}
		echo "</select>";

		$count = 0;
		foreach ($file_list as $file)
		{
			if (substr_compare($file, ".class", -strlen(".class"), strlen(".class"), true) == 0): continue; endif;
			$file_path = $student_path."/".$file;
			echo "<div class='code-block' style='display: none;' id='file$count-$user_id-code'>";
			echo "<pre>";
			$lines = file($file_path);
			$linenum = 0;
			foreach ($lines as $line)
			{
				echo "$linenum: &nbsp;&nbsp;&nbsp;".strip_tags($line);
				$linenum++;
			}

			echo "</pre>";
			echo "</div>";

			$count++;
		}
		?>
		<br><br>
		<b>Grading Rubric: </b>
		<form id="grading_rubric<?= $user_id ?>" method="post" action="process_grade.php" onsubmit='return submit_grading_form(<?= $user_id ?>)'>
			<table>
				<tr><td>Program Execution:</td><td><input type="text" name="gr1" id="gr1" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="2" /></td></tr>
				<tr><td>Program Specification:</td><td><input type="text" name="gr2" id="gr2" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="2" /></td></tr>
				<tr><td>Readability:</td><td><input type="text" name="gr3" id="gr3" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="2" /><br/></td></tr>
				<tr><td>Reusability:</td><td><input type="text" name="gr4" id="gr4" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="2" /></td></tr>
				<tr><td>Documentation:</td><td><input type="text" name="gr5" id="gr5" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="1" /></td></tr>
				<tr><td>Accountable Use of Class Time</td><td><input type="text" name="gr6" id="gr6" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="1" /></td></tr>
				<tr><td>Lab Report:</td><td><input type="text" name="gr7" id="gr7" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="2" /></td></tr>
				<tr><td>Timeliness:</td><td><input type="text" name="gr8" id="gr8" onkeyup="calculateTotal(<?= $user_id ?>)" size="2" maxlength="1" /></td></tr>
				<tr><td>Total:</td><td><input type='text' id='total<?= $user_id ?>' value = '0' disabled='disabled' size='2' /></td><td><input type='hidden' name='user_id' value='<?= $user_id ?>'><input type='hidden' name='assignment_id' value='<?= $assignment_id ?>'></td></tr>
				<tr><td style='vertical-align: top;'>Comments:</td><td><textarea name='comment' id='comment' rows=10 cols=40 style="resize: vertical;"></textarea></td></tr>
			</table>
			<input type="submit" class="button" value="Submit" id ="submit_grading_rubric"/>&nbsp;
			<input type="reset" class="button" value="Reset" id="reset_grading_rubric" onclick='reset_grading_form(<?= $user_id ?>)'/><br/>
			<? add_token(); ?>
		</form>
	<?


		echo "</div></li>";
	}
	echo "</ul>";

	?>
		<script>

		function calculateTotal(user_id){
			var test = 0;
			for(i = 1; i <= 8; i++){
				var form = $('#grading_rubric' + user_id);

				if(isNaN(parseInt($(form).find('#gr'+i).val()))) continue;
				test += parseInt($(form).find('#gr'+i).val());
			}
			var id = "#total" + user_id;
			$(id).attr("value",test);
		}
		function reset_grading_form(user_id){
			var id = "#total" + user_id;
			$(id).attr("value",0);
		}
		function submit_grading_form(user_id){

			var id = "#total" + user_id;
			var amt = $(id).val();
			var total = parseInt(amt);

			var allFilled = true;
			var id = "#grading_rubric" + user_id;
			var lists = "#grading_rubric" + user_id + ": input";

			$("form#" + id + " :input").each(function(){
				if ($(this).val() == ""){
					allFilled = false;
				}
			});

			if (!allFilled){
				alert("All fields must be filled out.");
				return false;
			}

			if (total > 70){
				alert("Invalid score.  Maximum score is 70.");
				return false;
			}
			else{
				$data = $(id).serialize();
				post("process_grade.php",$data,function(data){
					if (data.indexOf("error") != -1){
						$('#failure-message').show("slow");
						//alert(data);
					}
					else if (data.indexOf("success") != -1){
						$('#success-message').show("slow");
					}
					else{
						$('#caution-message').show("slow");	
					}
				});
				return false;
			}
		}

		var current_file = "";

		$(document).ready(function(){
			$("select").each(function(){
				$(this).change(function(){
					var id = $(this).find("option:selected").attr("id");
					var code = "#" + id + "-code";
					if (current_file != ""){
						$(current_file).hide("slow");
						$(current_file).css("display","none");
					}
					$(code).show("slow");
					current_file = code;
					var current_id = $(this).attr("id");
					$("select").each(function(){
						if ($(this).attr("id") != current_id){
							$(this).val("none");
						}
					});
				});


				$('.message-wrapper').click(function(){
					$(this).hide("slow");
				})

			});

			$("#student-files").flexipage({
				element: 	'li',
				perpage: 	1
			});
		});

		</script>
	<?
		

?>

<? get_footer(); ?>











