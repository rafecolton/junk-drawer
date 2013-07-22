<?php

/****************
	Advanced TODOs:
	- add ability to add / describe a major
	- 
	
	TODOs:
	- write welcome message / instructions
	- expand Add User to allow adding advisors or students (with file upload)
	- ability to edit advisor info (template for advisor individual settings)
	- ability to populate course database (with file upload)
	- fix css on manual_course_add
	- confirmation dialog on overriding a course
	
	
****************/

$type =  f3::get('PARAMS[type]');

switch ($type){
	
	case "welcome":
		show_welcome();
		break;
	case "add_user":
		show_add_user();
		break;
	case "add_major":
		show_add_major();
		break;
	case "manual_course_add":
		show_manual_course_add();
		break;
	case "edit_advisor":
		show_edit_advisor();
		break;
	case "add_courses":
		show_add_courses();
		break;
	case "unlock":
		unlock();
		break;
}

function unlock(){
	if ($_SESSION["usertype"] != "admin"):
		echo "</body>\n";
		echo "</html>";
		die;
	endif;
?>
	<script>
	function set_content()
	{
		$('#main_content').html(arguments[0]);
	}

	function request_content()
	{
		var type = $('#content_type').val();
		post('cpanel_content/' + type + '/','set_content');	
	}
	</script>
	<div id="main-container">
	<?php print_navigation(); ?>
	<select id="content_type">
	<option value="select">Select an option:</option>
	<option value="welcome">Welcome Message</option>
	<option value="add_user">Add User</option>
	<option value="manual_course_add">Add Course Manually</option>
	<option value="----------">----------</option>
	<option value="add_major">Add Major</option>
	<option value="edit_advisor">Edit Advisor</option>
	<option value="add_courses">Add Courses</option>
	</select>
	<button onclick="request_content()">Go</button>
	<br><br>
	<div id="main_content"></div>
	</div>
<?php	
	print_footer();
}

function show_welcome(){
?>
	<p style="width:600">Welcome to the Admin CPanel page.  Currently, the only functionality that exists here is the ability to add an admin user.  
<?php
}

function show_add_user(){
?>
	Fill this out to add a new admin user:<br>
	<form id = "add_user_form">
	Username: <input type="text" name = "username" id = "username" onkeypress="eval_form(event,'#submitbutton')">&nbsp;
	Password: <input type ="password" name = "password" id = "password" onkeypress="eval_form(event,'#submitbutton')">&nbsp;
	Email: <input type = "email" name = "email" id = "email" onkeypress = "eval_form(event,'#submitbutton')">&nbsp;
	<input type = "hidden" name = "usertype" id = "usertype" value = "admin">
	<input type = "button" name = "submitbutton" id = "submitbutton" value = "Submit" onclick = "add_user()">
	</form>
	<br><div id="info_message" class="success_message"></div>
	<script>
	function add_user(){
		$('#info_message').html("");
		var $formdata = $("#add_user_form").serialize();
		post("add_user/",$formdata,'confirm_addition');
	}
	
	function confirm_addition(){
		$('#info_message').html("User successfully added");
		$('#username').val("");
		$('#password').val("");
		$('#email').val("");	
	}
	
	</script>
	
<?php
}

function show_manual_course_add(){
?>
	Fill this out to add a class to the database manually:<br><br>
	<form id='add_course_form'>
	Department: <input type='text' maxlength='10' name='department' id='department' onkeypress="eval_form(event,'#submitbutton')" placeholder='Example: CS'>&nbsp;
	Course No: <input type='text' maxlength='4' name='courseNo' id='courseNo' onkeypress="eval_form(event,'#submitbutton')" placeholder='Example: 1950'><br><br>
	Name: <input type='text' name='name' id='name' onkeypress="eval_form(event,'#submitbutton')" placeholder='Example: Directed Study'><br><br>
	Description: <br><textarea rows='5' cols='75' name='description' id='description'></textarea><br><br>
	No Credits: <input type='text' maxlength='1' name='noCredits' id='noCredits' onkeypress="eval_form(event,'#submitbutton')">&nbsp;
	Grade Option: <input type='text' maxlength='1' name='gradeOption' id='gradeOption' onkeypress="eval_form(event,'#submitbutton')"> <span style="color:gray;"><em>(options: (s)atisfactory/no credit, (l)etter grade, (b)oth)</em></span><br><br>
	Prerequisites: <br><textarea placeholder='Please enter all courses on one line, separated by commas.  Example: CS 0401, CS 0441, CS 0445' id='prerequisites' name='prerequisites' rows='5' cols='75'></textarea><br><br>
	Corequisites: <br><textarea placeholder='Please use the same format as the prerequisites field.' id='corequisites' name='corequisites' rows='5' cols='75'></textarea><br><br>
	<b>note: if the course/prerequisites/corequisites already exist in the database, they will be replaced</b>
	<br><br>
	<input type='button' name='submitbutton' id='submitbutton' value='Submit' onclick='add_course()'>
	<input type='reset' name='resetbutton' id='resetbutton' value='Reset'>
	</form>
	<br><div id="info_message" class="success_message"></div>
	<script>
	function add_course(){
		if (arguments.length == 0){
			var grade = $('#gradeOption').val().toUpperCase();
			if (!(grade == "S" || grade == "L" || grade == "O" || grade == "B")){
				alert("Invalid Grade Option");
			}
			else{
				$('#info_message').html("");
				$('#gradeOption').val($('#gradeOption').val().toUpperCase());
				$('#department').val($('#department').val().toUpperCase());
				$('#prerequisites').val($('#prerequisites').val().toUpperCase());
				$('#corequisites').val($('#corequisites').val().toUpperCase());
				var $formdata = $("#add_course_form").serialize();
				post("cpanel/course_add/manual/",$formdata,'add_course');
			}
		}
		
		else{
			$('#info_message').html(arguments[0]);
			//$('#resetbutton').click();
		}
	}
	</script>
<?php
}

function show_add_major(){
?>
	Here would be a way to add a new major.
<?php
}

function show_edit_advisor(){
?>
	Here would be a way to edit a selected advisor.
<?php
}

function show_add_courses(){
?>
	Here would be a way to add courses (but uploading a file).
<?php
}

?>