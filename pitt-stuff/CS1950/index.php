<?php
#Rafe Colton

/****************
	TODOs exist in the following files:
	- fp_unlocked.php
	- control_panel_content.php
	- functions.php (currently contains settings TODOs)
	- pitt.css
	- fp_class_search.php
	
	Pages to Add:
	- advising sessions
	- schedule an advising session
	- settings
	- forgot password on login
	
	
****************/

#gets required pages
session_start();
require_once("functions.php");
require_once("_f3/pages/general/dbconfig.php");
load_base();

#initializes some F3 variables
F3::set('DEBUG',0);
F3::set('POST',F3::scrub($_POST));
F3::set('GET',F3::scrub($_GET));
F3::set('DB',
	new DB("mysql:host=$dbhost;dbname=$dbname","$dbuser","$dbpass")
);
initialize_database();

#content request for main page
F3::route('GET /',"main.php");


/*
To create a new page with a lock:
	•	create page
	•	create route to page with GET request
	•	include lock() function
		⁃	arg1: routing address to validator script (set up with POST request)
		⁃	arg2: routing address to content script (set up with POST request)
	•	on unlocking/content script script page, create function that prints out the content to be visible once the page is unlocked
*/

//user management
F3::route('POST /universal_validator/','_f3/pages/user_mgmt/user_mgmt_validator.php');
F3::route('POST /add_user/','_f3/pages/user_mgmt/user_mgmt_add.php');

//general
F3::route('POST /logout','_f3/pages/general/logout_request.php');
F3::route('GET /logout','_f3/pages/general/logout_page.php');

//content request for cpanel (LOCKED)
F3::route('GET /cpanel','_f3/pages/cpanel/control_panel.php'); //control panel
F3::route('POST /cpanel_content/@type/','_f3/pages/cpanel/control_panel_content.php'); //provides main content
F3::route('POST /cpanel/course_add/@type/','_f3/pages/cpanel/control_panel_course_add.php'); //add a course to the database

//future planning page
F3::route('GET /futureplan','_f3/pages/futureplan/fp_locked.php'); //page
F3::route('POST /fplan_unlock','_f3/pages/futureplan/fp_unlocked.php'); //future planning content
F3::route('PUT /fplan_save','_f3/pages/futureplan/fp_save.php');
F3::route('GET /searchlist/@course/','_f3/pages/futureplan/fp_class_search.php'); //page for autocomplete for class list
F3::route('GET /searchlist/@courseID/@uniqueID','_f3/pages/futureplan/fp_class_search.php');

F3::run();



?>