<div id="main-container">
<?php
/****************
	Advanced TODOs:
	- autoPopulate()
	- duplicate as new
	- evaluate against major
		
	TODOs:
	- addSemester() function
	- evaluate-against-major functions
	- ability of advisor to set student write priviliges (also, to select a student)
	- ability to change year title (fr, so, jr, etc.) w/ contenteditable
	- undo & redo for different types of actions
	- bug reporting tool
	- ability to save Fp sessions
		. fp_save.php()
		. offer to save when creating new
		. make sure nickname is set (when creating new or when saving)
	- adding nicknames to fp session
	- course change color to indicate that crucial element has been changed / manually change course color with color picker
	- change color of text in tooltip that has been edited
	- navigation menu (add drop-down)
	- application menu
	- error reporting tool incorporated into f3.js
		
COMPLETED:
- (12/2) fixed quirk: With contenteditable fields, in Firefox, if the user removes all text, they are no longer able to type in the field and must create a new course.
		This was fixed by setting up 1 second timer, where if the user empties the text in a field and does not enter new text for 1 second, it puts a "n/a" in there.
- (12/2) fixed bug: "n/a" in field in tooltip now recognized correctly
- (12/2) fixed bug: with changes to countsAs field - now validates after every change instead of just after the first (without bluring/focusing in between)
- (12/2) fixed bug: populatePlan sets undo/redo buttons based on whether or not the respective stacks have contents when the Fp is loaded
- (12/2) Fp Object data now stored in longtext field to avoid length issues
- (12/12) Added pitt.css, filled with styling to look like pitt
- (12/12) fixed bug: when course was loaded from database, countsAs, previousCountsAs were not being populated correctly
- (12/12) fixed bug: "selected" class on semesters was not being removed when the course was released into the semester
- (12/12) organized TODOs into their respective pages and put list of where there are TODOs into index.php
	
****************/
?>
<?php print_navigation(); ?>
<select>
<option value = "test">Select a session:</option>
</select>
&nbsp;<button id = "new_button" onclick = "createNew()">New +</button>
&nbsp;<button disabled>Duplicate As New</button>
&nbsp;<button disabled>Delete Plan</button>
&nbsp;<button onclick='test()'>Reveal Your Secrets!</button>
&nbsp;<button id = "save_button" onclick="saving()"><span id="save">Save</span><span id="saving" style="display:none;">Saving... <img height="12px" src="_images/loading.gif"></span><span id="saved" style="display:none">Saved &#x2713;</span></button>
&nbsp;<span id="save_message"></span>

<div id="fp_buttons"></div><br>
<div id="fp_wrapper"></div>

<script>
//test()
function test(){
	consolidate();
	alert(JSON.stringify($fp));
}

//ready()
function ready(){
	//for save button and any other "on load" functions
	$('#populate').attr('disabled','disabled');
	t3 = setTimeout("$('#save_button').click()",30000);
	t2 = setTimeout("saved_30()",60000);
	$('#save_button').removeAttr('disabled');
}

//createNew()
//has TODO
function createNew(){
	if (courseList.length == 0) loadCourseList();
	$fp = new Fp();	
	
	/****************
	
	TODO: prompt for save

	****************/
		
	populatePlan($fp);
}

//enable()
//sets up the sortable function and explicitly enables sorting
//also contains the CSS3 animation for dragging and dropping
function enable(){
	$(function(){
		$("ul.semester.normal, ul.semester.summer").sortable({
			connectWith: 	"ul.semester.normal, ul.semester.summer, ul.trash",
			cursor:			"move",
			items:			"li.course"
		});

		$("#searchlist, #trash").sortable({
			connectWith: 	"ul",
			cursor:			"move",
			items:			"li.course"

		});
		
		$("ul").sortable({
			receive: function(event, ui) {
				if (!$fp.undoInProgress && !$fp.redoInProgress){
					
					//load undo stack
					var actionObj = new Object();
					actionObj.from = ui.sender.attr('id');
					actionObj.to = $(this).closest('ul').attr('id');
					actionObj.item = $(ui.item).attr("id");
					actionObj.type = "move";
					actionObj.prevClass = $(ui.item).attr("class");
					$fp.undoStack.push(actionObj);	
					$('#undo').removeAttr('disabled');
					
					$("#" + actionObj.to).toggleClass('selected',false);
					
					//validate course, validate on course	
					validateCourse(actionObj.item, actionObj.to);	
					validateOnCourse(actionObj.item);		
					
					//reset redo stack
					$fp.redoStack = new Array();
					$('#redo').attr('disabled','disabled');
						
				}				
			}
		});
		
		$("ul.semester.normal, ul.semester.summer").sortable({
			over: function(event, ui){
				var ul = $(this).closest('ul');
				$(ul).toggleClass('selected',true);
			},
			
			out: function(event, ui){
				var ul = $(this).closest('ul');
				$(ul).toggleClass('selected',false);
			}
		});
	});
		
	$("ul.semester.normal, ul.semester.summer, #searchlist, #trash").sortable("option","disabled",false);
		
	$('#search').autocomplete({source: courseList});

}

//disable()
//explicitly disables user's ability to sort on the current page
function disable(){
	$(function(){
		$("ul.semester.normal, ul.semester.summer, #searchlist, #trash").sortable("option","disabled",true);
	});
	
	$( "#search" ).autocomplete("option","disabled",true);
}

//validateCourse(item, currentLocation)
function validateCourse(item, currentLocation){

	var uniqueID = "#" + item;
	var courseObject = $fp.courseObject[item];
	var prerequisites = courseObject.prerequisites;
	var corequisites = courseObject.corequisites;
		
	if ((!corequisites || corequisites.toString() == "" || corequisites.toString().toUpperCase() == "N/A") 
			&& (!prerequisites || prerequisites.toString() == "" || prerequisites.toString().toUpperCase() == "N/A")){
		$(uniqueID).toggleClass('ok',true);	
		$(uniqueID).toggleClass('not_ok',false);
	}
	
	else{
		var validateP = new Object();
		var validateC = new Object();
		var validatedP = true;
		var validatedC = true;
	
		if (!(!prerequisites || prerequisites.toString() == "" || prerequisites.toString().toUpperCase() == "N/A")){
			
			//load validateP array
			for (req in prerequisites){
				if (prerequisites[req] && prerequisites[req] != "N/A"  && prerequisites[req] != ""){
					validateP[prerequisites[req]] = false;
				}
			}
			
			//iterate through the items and set them
			for (req in validateP){
				for (var x=0; x<$fp.numSemesters; x++){
					var semesterName = "semester";
					semesterName += x;
					if (semesterName == currentLocation) break;
					$('#' + semesterName + " li").each(function(index, element){
						var tempID = $(element).attr("id");
						var courseID = $fp.courseObject[tempID].countsAs.toUpperCase();
						if (courseID == req){
							validateP[req] = true;	
						}
					});	
				}
			}	
			
			//set results
			for (req in validateP){
				if (!validateP[req]) validatedP = false;
			}
			
		}
		
		if (!(!corequisites || corequisites.toString() == "" || corequisites.toString().toUpperCase() == "N/A")){
			
			//load validateC array
			for (req in corequisites){
				if (corequisites[req] && corequisites[req] != "N/A"  && corequisites[req] != ""){
					validateC[corequisites[req]] = false;
				}
			}
			
			//iterate through items and set them
			for (req in validateC){
				for (var x=0; x<$fp.numSemesters; x++){
					var semesterName = "semester";
					semesterName += x;					
					$('#' + semesterName + " li").each(function(index, element){
						var tempID = $(element).attr("id");
						var courseID = $fp.courseObject[tempID].countsAs.toUpperCase();
						if (courseID == req){
							validateC[req] = true;	
						}
					});	
					if (semesterName == currentLocation) break;
				}
			}	
			
			//set results
			for (req in validateC){
				if (!validateC[req]) validatedC = false;
			}
		}
			
		//set based on results		
		if (validatedP && validatedC){
			$(uniqueID).toggleClass('ok',true);	
			$(uniqueID).toggleClass('not_ok',false);
		}
		
		else{
			$(uniqueID).toggleClass('ok',false);
			$(uniqueID).toggleClass('not_ok',true);
		}
	}
}

//validateOnCourse(item)
/*
This function should "validate forward" - it should check all courses in its same semester and going forward.
If any of them require it as a prereq and they are not set to "ok" already, validateCourse should be called on them.
*/
function validateOnCourse(item){
	
	var courseID = $fp.courseObject[item].countsAs.toUpperCase();
	var prevID = $fp.courseObject[item].previousCountsAs.toUpperCase();
	
	for (var x=0; x<$fp.numSemesters; x++){
		var semesterName = "semester";
		semesterName += x;
		$('#' + semesterName + " li").each(function(index, element){
			var uniqueID = $(element).attr("id");
			var prereqs = $fp.courseObject[uniqueID].prerequisites;
			var coreqs = $fp.courseObject[uniqueID].corequisites;
			var needsValidation = false;
			
			for (var req in prereqs){
				if (prereqs[req] == courseID || prereqs[req] == prevID){
					needsValidation = true;	
				}
			}
			
			for (var req in coreqs){
				if (coreqs[req] == courseID || prereqs[req] == prevID){
					needsValidation = true;	
				}
			}
			
			if (needsValidation){
				validateCourse(uniqueID, semesterName);	
			}
			
		});
	}
	
	return;
}

//addCourse()
function addCourse(){		
	var field = $('#search').val();
	field = field.toUpperCase();
	if (!(field == "") && $('#searchlist li').size() < 16 && $fp.studentWritePriv){
		$('#search').val("");
		var uniqueID = field + courseCount;
		uniqueID = uniqueID.split(' ').join('');
		var title = "test text<br>more test text";
		$("#searchlist").append("<li class='course' id = '" + uniqueID +"'>" + field + "</li>"); //title='" + title + "'
		courseCount++;
		
		/*
		Constructor does the following:
		1. Initializes all fields in the object to the empty string.
		2. Sends a request to the server to load course information from the database if it is present.
		3. If it is present, the callback function replaces the object in courseObject with the new one returned from the server.
		*/
		new Course(field, uniqueID);
	}
}

//autoPopulate()
//has TODO
function autoPopulate(){
	alert("This button currently does nothing.");
	$('#populate').attr('disabled','disabled');	
	/****************
		
		TODO: fill in this function
					
	****************/
}

//undo()
function undo(){
	$('#redo').removeAttr('disabled');
	
	$fp.undoInProgress = true;
	actionObj = $fp.undoStack.pop();
	
	switch(actionObj.type){
	
		case "move":
			var from = '#' + actionObj.from;
			var to = '#' + actionObj.to;
			var item = '#' + actionObj.item;
			
			$(from).append($(item));
			var prevClass = actionObj.prevClass;
			actionObj.prevClass = $(item).attr("class");
			$(item).attr("class",prevClass);
			
			validateOnCourse(actionObj.item);
			
			break;	
	}
	
	$fp.redoStack.push(actionObj);
	$fp.undoInProgress = false; 
	
	if ($fp.undoStack.length == 0) $('#undo').attr('disabled','disabled');
}

//redo()
function redo(){
	$('#undo').removeAttr('disabled');
	
	$fp.redoInProgress = true;
	actionObj = $fp.redoStack.pop();
	
	switch(actionObj.type){
	
		case "move":
			var from = '#' + actionObj.from;
			var to = '#' + actionObj.to;
			var item = '#' + actionObj.item;
			var prevClass = actionObj.prevClass;
			actionObj.prevClass = $(item).attr("class");
			$(item).attr("class",prevClass);
			$(to).append($(item));	
			
			validateOnCourse(actionObj.item);
		
			break;	
	}
	
	$fp.undoStack.push(actionObj);
	$fp.redoInProgress = false;
	
	if ($fp.redoStack.length == 0) $('#redo').attr('disabled','disabled');
}

//saving()
//sets the button when saving... is in progress
//is called when button is clicked
function saving(){
	$('#save').css("display","none");
	$('#saved').css("display","none");
	$('#saving').css("display","block");
	$('#save_message').html("");
	clearTimeout(t2);
	clearTimeout(t3);
	
	//loads the $fp object with all info necessary
	consolidate();
	
	var $data = "";
	
	if ($fp != null){
		$data = JSON.stringify($fp);
	}
		
	var $data_temp = "";
	var $timestamp = "";
	$('#save_button').attr('disabled','disabled');
	put('fplan_save',$data_temp,'save_response');
}

//save_response()
//sets 1 second delay when server responds afer save
function save_response(){
	var t1 = setTimeout("saved()",1000);
}

//saved()
//sets button to indicate that course has saved
//sets timeout for telling the user the document will auto-save in 30 seconds
function saved(){
	$('#saving').css("display","none");
	$('#saved').css("display","block");
	$('#save_button').removeAttr('disabled');
	$('#save_message').html("Saved within the last 30 seconds");
	var t2 = setTimeout("saved_30()",30000);
}

//saved_30()
//tells the user the document will be saved in 30 seconds
//sets up timeout to click the button in 30 seconds
function saved_30(){
	$('#saved').css("display","none");
	$('#save').css("display","block");
	$('#save_message').html("Document will save again in 30 seconds");
	var t3 = setTimeout("$('#save_button').click()",30000);
}

//consolidate()
//consolidates all of the fields into the $fp object before saving
function consolidate(){
	
	if ($fp == null) return;
	
	$fp.courseList = new Array();
	
	//consolidates the Array for each $fp.semester#
	for (var x=0; x<$fp.numSemesters; x++){
		var semesterName = "semester";
		semesterName += x;
		$fp[semesterName] = new Array();
		$('#' + semesterName + " li").each(function(index, element){
			$fp[semesterName].push($(element).outerHTML());
			//alert($(element).outerHTML());
			$fp.courseList.push($(element).html());
		});
	}
	
	//consolidates the searchList Array
	$fp.searchList = new Array();
	$('#searchlist li').each(function(index, element){
			if (index != 0){
				$fp.searchList.push($(element).outerHTML());
				$fp.courseList.push($(element).html());
			}
	});
	
	//consolidates the trashList Array
	$fp.trashList = new Array();
	$('#trash li').each(function(index, element){
			$fp.trashList.push($(element).outerHTML());
			$fp.courseList.push($(element).html());
	});
}

</script>

<script>
var $fp = null;
var courseCount = 0;
var appendBuffer = "";
var courseList = new Array();
loadCourseList();
ready();

</script>
</div>
<?php print_footer(); ?>