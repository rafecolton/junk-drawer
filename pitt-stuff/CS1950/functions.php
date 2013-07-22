<?php
#Rafe Colton

/****************
	TODOs:
	- better navigation (drop-down)
	- forgot password feature
	- expiration on passwords
	
	Settings TODOs:
	- colorblind mode
	- autosave (on/off)
	- select major
	- edit personal info
	- hide summers
	- change password
	- general requirements document for majors
	
****************/

function load_base(){
	require_once('_f3/lib/base.php');
}

//create database calls
function initialize_database(){
	DB::sql("CREATE TABLE IF NOT EXISTS Admin_Users (
		Username CHAR(10) NOT NULL, 
		Password TEXT NOT NULL, 
		Email TEXT NOT NULL, 
		CONSTRAINT admin_pk PRIMARY KEY (Username)
		);"
		#hash passwords
		#timestamp for database for temporary passwords
	);
	
	DB::sql("CREATE TABLE IF NOT EXISTS All_Users (
		Username CHAR(10) not null,
		password text not null,
		User_Type enum ('admin','student','advisor','demo') not null,
		constraint all_users_pk primary key (Username)
		);"
	);
	
	DB::sql("CREATE TABLE IF NOT EXISTS Future_Plans (
		Username char(10) not null,
		Timestamp int not null,
		Data longtext,
		Nickname text,
		Student_Write_Priv boolean,
		CONSTRAINT future_plans_pk primary key (Username, Timestamp)
		);"
	);		
	
	#note: for grad_option, S = satisfactory/no credit, L = letter grade, B = both, O = other
	DB::sql("CREATE TABLE IF NOT EXISTS Courses (
		Course_No int(4) not null,
		Department char(10) not null,
		Description text,
		Name text,
		Grade_Option ENUM ('S','L','O','B','s','l','o','b'),
		No_Credits int,
		CONSTRAINT courses_pk primary key (Course_No, Department)
		);"
	);
	
	DB::sql("CREATE TABLE IF NOT EXISTS Prerequisites (
		Course_No int(4) not null,
		Department char(10) not null,
		P_Course_No int(4) not null,
		P_Department char(10) not null,
		CONSTRAINT prerequisites_pk primary key (Course_No, Department, P_Course_No, P_Department)
		);"
	);
	
	DB::sql("CREATE TABLE IF NOT EXISTS Corequisites (
		Course_No int(4) not null,
		Department char(10) not null,
		C_Course_No int(4) not null,
		C_Department char(10) not null,
		CONSTRAINT prerequisites_pk primary key (Course_No, Department, C_Course_No, C_Department)
		);"
	);
}

function restrict_access_to_AJAX(){
	define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

	if(!IS_AJAX) {
    	echo "invalid access";
		die;
	}
}

function print_links(){
	#css
	echo "<link type='text/css' href='_css/jquery_theme.css' rel='stylesheet'>\n"; 				#for jQuery theme
	echo "<link type='text/css' href='_css/styling.css' rel='stylesheet'>\n"; 					#mine / fp page
	echo "<link type='text/css' href='_css/pitt.css' rel='stylesheet'>\n"; 						#pitt
	
	#other
	echo "<link rel='shortcut icon' href='_images/_pitt/favicon.ico'>\n";						#pitt icon
	
	#javascript
	echo "<script src='_js/jquery.js'></script>\n"; 											#jQuery
	echo "<script src='_js/jquery_ui.js'></script>\n";											#jQuery UI
	echo "<script src='_js/f3.js'></script>\n"; 												#my f3 functions for AJAX calls
	echo "<script src='_js/functions.js'></script>\n"; 											#my other general functions
	echo "<script src='_js/jquery.tools.min.js'></script>\n";									#for tooltip (http://flowplayer.org/tools/tooltip/index.html)
}

function print_navigation(){
?>
	<div id='pitt-header' class='blue'>
	<a href="http://www.pitt.edu/" title="University of Pittsburgh" id="p-link">University of Pittsburgh</a>
	</div>
	
	<ul id="pitt-links"><li id="p-home">
	<a href='futureplan'>Future Planning</a>&nbsp;|&nbsp;
	<a href='cpanel'>Control Panel</a>&nbsp;|&nbsp;
	<a id='logout' href='javascript:logout()'>Logout</a>
	</li></ul>
	
	<script>
	function logout(){
		post('logout',function(){
			var data = arguments[0];
			if (data == "success"){
					window.location.href = window.location.href;
			}
			else{
				$('#logout').attr("href","logout");
				alert("Logout failed.  Please try again.");
			}
		});
	}
	</script>
<?php
}

function print_footer(){
?>	
	<div id="footer">
  		<p class="left">Copyright 2011 | Site by <a href="http://rafe.in/pittsburgh">Rafe Colton</a></p>
  		<p class="right"><a href="mailto:rhc8@pitt.edu">rhc8@pitt.edu</a> 
	</div>
<?php
}


function lock($unlocking_url){
	$validation_url = "universal_validator/";
?>


	<div id="lock_wrapper">
	<div id="lock-positioning-wrapper">
	<div id="lock-style">
		<div id='pitt-header' class='blue'>
			<a href="http://www.pitt.edu/" title="University of Pittsburgh" id="p-link">University of Pittsburgh</a>
		</div>
	<div id="login-form-wrapper">
	<form id="login_form" method="get">
	Username<br>
	<input type="text" name="username" id="username" onkeypress="eval_form(event,'#login-submitbutton')"><br><br>
	
	Password<br>
	<input type="password" name="password" id="password" onkeypress="eval_form(event,'#login-submitbutton')"><br><br>
	<input type="button" value="Submit" id = "login-submitbutton" onclick = "submit_unlock_request()">&nbsp;<input type="button" id="login-resetbutton" onclick="reset_form()" value="Reset"><br>
	</form>
	<div id="error_message" style="color:red"></div>
	</div>
	
	</div>
	<script>
	
	function reset_form(){
		$('#error_message').html("");
		$('#username').val("");	
		$('#password').val("");	
	}
	
	function submit_unlock_request(){
		var $formdata = $("#login_form").serialize();
		post('<?php echo $validation_url; ?>',$formdata,function(){
			var $data = arguments[0];
			if ($data == "true"){
				post('<?php echo $unlocking_url; ?>',function(){
					$('#lock_wrapper').html(arguments[0]);
				});
			}
			else{
				$('#error_message').html($data);	
			}
		});	
	}
	</script>
	</div></div>
<?php	
}


?>