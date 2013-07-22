//setup for $().outerHTML()
(function($) {
  $.fn.outerHTML = function() {
    return $(this).clone().wrap('<div></div>').parent().html();
  }
})(jQuery);

//eval_form(e, submitbutton)
function eval_form(e, submitbutton){
	var keynum;
	if(window.event){
		keynum = e.keyCode;
	}
	else if(e.which){
		keynum = e.which;
	}
	
	if (keynum == 13){
		$(submitbutton).click();
	}	
}

//loadCourseList()
//loads course list from database into JavaScript variable
function loadCourseList(){
	get("searchlist/load_list/", function(){
		courseList = arguments[0].split(",");
	});
}

//Fp()
//constructor for Fp Object
function Fp(){

	for (var x=0; x<12; x++){
		var key = "semester";
		key += x;
		this[key] = new Array(); //each semester has its own array that contains a list of the classes it contains
	}
	
	this.semestersElapsed = 0; //to be implemented later
	this.searchList = new Array(); //list of classes in the searchlist
	this.trashList = new Array();
	this.numSemesters = 12; //the number of semester <ul> objects on the page
	this.undoStack = new Array(); 
	this.redoStack = new Array();
	this.undoInProgress = false;
	this.redoInProgress = false;
	this.courseObject = new Object(); //each field of this object will be a "class" that will have info about itself - prerequisites, description, etc
	this.timestamp = new Date().getTime(); //to be used as part of the primary key
	this.studentWritePriv = true;
	this.nickname = null;
	this.isNew = true;
}

//Course(courseID, uniqueID)
//constructor for Course Object
//takes care of call to database to load tooltip info
function Course(courseID, uniqueID){
	this.department = "n/a";
	this.courseNo = "n/a";
	this.uniqueID = uniqueID;
	this.courseID = courseID;
	this.courseString = "n/a";
	this.name = "n/a";
	this.description = "n/a";
	this.noCredits = "n/a";
	this.gradeOption = "n/a";
	this.prerequisites = "n/a";
	this.corequisites = "n/a";
	this.countsAs = courseID;
	this.previousCountsAs = courseID;
	
	this.duplicate = function(){
		var temp = new Course();
		$.each(this, function(index, value){
			temp[index] = value;
		});
		return temp;
	};
	
	$fp.courseObject[uniqueID] = this;
		
	get("searchlist/" + courseID + "/" + uniqueID, function(){
			if (arguments[0].indexOf("error") != -1){
			//error
			//alert("error with loading course from database");
		}
		
		else{		
			var tempObject = JSON.parse(arguments[0]);
			var uniqueID = tempObject.uniqueID;
			var courseID = tempObject.courseID;
			tempObject = null;
			
			//updates the courseObject subobject for the unique course to reflect the correct data (replaces old object)
			$fp.courseObject[uniqueID] = JSON.parse(arguments[0]);
			$fp.courseObject[uniqueID].countsAs = courseID;
					
			setTooltip(uniqueID);
		}
	});
}

//setTooltip(uniqueID)
//includes setup for action for [contenteditable] events
function setTooltip(uniqueID){
		var tempObject = $fp.courseObject[uniqueID];		
		var courseID = tempObject.courseID;
		var uniqueID = tempObject.uniqueID;
		var courseNo = tempObject.courseNo;
		var department = tempObject.department;
		var name = tempObject.name;
		var description = tempObject.description;
		var noCredits = tempObject.noCredits;
		var gradeOption = tempObject.gradeOption;
		var prerequisites = tempObject.prerequisites;
		var corequisites = tempObject.corequisites;
		var countsAs = tempObject.countsAs;
		var title = "<div style='max-height: 150px; overflow: auto; position: relative;'>";
		
		title += "<b>Name:</b> <span class='editable' contenteditable='true' id='tooltip_name_" + uniqueID + "'>" + name + "</span><br>";
		title += "<b>Department & Course No:</b> <span class='editable' contenteditable='true' id='tooltip_department_course_" + uniqueID + "'>" + department + " " + courseNo + "</span><br>";
		title += "<b>Counts As:</b> <span class='editable' contenteditable='true' id='tooltip_countsAs_" + uniqueID + "'>" + countsAs + "</span><br>";
		title += "<b>Description:</b> <span class='editable' contenteditable='true' id='tooltip_description_" + uniqueID + "'>" + description + "</span><br>";
		title += "<b>No. Credits:</b> <span class='editable' contenteditable='true' id='tooltip_noCredits_" + uniqueID + "'>" + noCredits + "</span><br>";
		title += "<b>Grade Option:</b> <span class='editable' contenteditable='true' id='tooltip_gradeOption_" + uniqueID + "'>";
		switch (gradeOption){
			case "S":
				title += "(S)atisfactory/No Credit</span><br>";
				break;
				
			case "L":
				title += "(L)etter Grade</span><br>";
				break;
				
			case "B":
				title += "(B)oth S/NC or Letter Grade</span><br>";
				break;	
			
			case "O":
				title += "(O)ther</span><br>";
				break;
			
			default:
				title += "</span><br>";
				break;
		}
		 
		title += "<b>Prerequisites:</b> <span class='editable' contenteditable='true' id='tooltip_prerequisites_" + uniqueID + "'>" + prerequisites + "</span><br>";
		title += "<b>Corequisites:</b> <span class='editable' contenteditable='true' id='tooltip_corequisites_" + uniqueID + "'>" + corequisites + "</span><br>";
		title += "<br></div>";
		 
		//updates the courseObject subobject for the unique course to reflect the correct data (replaces old object)
								
		$('#' + uniqueID).prop("title",title);
		
		$('#' + uniqueID).tooltip({
			position: 	'top center',
			offset:		[10, 50],
			tipClass:	'tooltip',
			events: 	{
				def:		'click, mouseout mousedown'	
			},
			onBeforeShow: function(){
				var item = this.getTrigger();
				$(item).toggleClass('selected',true);
			},
			
			onHide: function(){
				var item = this.getTrigger();
				$(item).toggleClass('selected',false);
			}
		});
		
		//function for editing
		$('[contenteditable]').live('focus', function() {
    		var $this = $(this);
   			$this.data('before', $this.html());
			
			var idArray = $this.attr("id").split("_");
				
			var type = idArray[1];
			var uniqueID = idArray[2];
			
			if (type == "countsAs"){
				$fp.courseObject[uniqueID].previousCountsAs = $this.html();	
			}
			
			return $this;
		}).live('blur keyup paste', function(event) {
			var $this = $(this);
    		if ($this.data('before') !== $this.html()) {
       							
				$this.data('before', $this.html());
				
				var change = jQuery.Event("change");
				
				//**********<event_handling>**********//
					
				var idArray = $this.attr("id").split("_");
				
				var type = idArray[1];
				var uniqueID = idArray[2];
				var selector = "#" + uniqueID;
								
				if (type != "corequisites" && type != "prerequisites" && type != "countsAs"){
					$fp.courseObject[uniqueID][type] = $this.html();	
				}
				
				else if (type == "countsAs"){
					$fp.courseObject[uniqueID][type] = $this.html();
					validateOnCourse(uniqueID);
					$fp.courseObject[uniqueID].previousCountsAs = $this.html();
				}
				
				else{
					var tempString = $this.text();
					var tempArray = tempString.split(",");
					for (var req in tempArray){
						var temp = tempArray[req].toUpperCase();
						tempArray[req] = trim(temp);
					}
					$fp.courseObject[uniqueID][type] = tempArray; 
					
					var currentLocation = $(selector).closest('ul').attr('id');
					validateCourse(uniqueID, currentLocation);
				}
				
				if ($this.text() == ""){
					var blankFieldTimeout = setTimeout(function(){
						if ($this.text() == "") $this.text("n/a");
					},1000);
				}
				 
				else{
					if (window.blankFieldTimeout){
						clearTimeout(blankFieldTimeout);	
					}
				}
				//**********</event_handling>**********//
    		}
   		 return $this;
		});
}

//trim(source)
function trim(source) {
	source = source.replace( /^\s+/g, "" );// strip leading
	return source.replace( /\s+$/g, "" );// strip trailing
}

//setUpKeyboardShortcuts()
function setUpKeyboardShortcuts(){
		
	document.onkeydown = function(e){ 
			var keynum;
			if(window.event){
				keynum = e.keyCode;
			}
			else if(e.which){
				keynum = e.which;
			}
						
			if (keynum == 90 && (e.metaKey || e.ctrlKey) && !e.shiftKey) {
				if ($('#undo').attr("disabled") != "disabled"){
					$('#undo').click(); 
				}
				e.preventDefault();
				return false;
			} //ctrl+z or cmd+z
			
			if (keynum == 90 && (e.metaKey || e.ctrlKey) && e.shiftKey) {
				if ($('#redo').attr("disabled") != "disabled"){
					$('#redo').click(); 
				}
				e.preventDefault();
				return false;
			} //ctrl+shift+z or cmd+shift+z
			
			if (keynum == 89 && (e.metaKey || e.ctrlKey)) {
				if ($('#redo').attr("disabled") != "disabled"){
					$('#redo').click(); 
				}
				e.preventDefault();
				return false;
			} //ctrl+y or cmd+y
			
			if (keynum == 83 && (e.metaKey || e.ctrlKey)) {
				if ($("#save_button").attr("disabled") != "disabled") $("#save_button").click();
				e.preventDefault();
				return false;
			} //ctrl+s or cmd+s*/
	}
}

//populatePlan(plan)
//argument is a JavaScript Fp Object
function populatePlan(plan){
	
	//clears out the current fp_wrapper content
	$('#fp_wrapper').html("");
		
	//add undo/redo buttons
	append("<div id='buttons'>");
	append("<button id='undo' onclick='undo()'>Undo</button>&nbsp;<button id='redo' onclick='redo()'>Redo</button>");
	append("&nbsp;<button id='populate' onclick='autoPopulate()' disabled>Auto-Populate</button>&nbsp;<button id = 'evaluate' disabled>Evaluate Against Major</button>");
	append("</div><br><br><br>");
	
	var numcells = plan.numSemesters;
	var cells = new Array();
	
	//prep "cells" array"
	for (var semester=0; semester<numcells; semester++){
			cells[semester] = "<ul id='semester" + semester + "'";
			
			if (semester % 3 == 2 && semester != numcells-1){
				cells[semester] += " class='semester summer'";
			}
			else if (semester == numcells-1){
				cells[semester] += " class='semester none'";
			}
			else{
				cells[semester] += " class='semester normal'";
			}
			
			if (semester != numcells-1){
				cells[semester] += ">";
				
				var semVar = "semester" + semester;
								
				var semArray = plan[semVar];
																
				for (course in semArray){
					cells[semester] += semArray[course];	
				}
	
				cells[semester] += "</ul>";
			}
			else{
				cells[semester] += "><span class='new_plus'>+</span></ul>";
			}
	}
	
	//append header row
	append("<table id='planTable' border='0'><tr><th></th><th>Fall</th><th>Spring</th><th>Summer</th></tr>");
	
	//add first row manually (to facilitate adding the searchlist)
	//add the searchlist
	append("<tr><th width='50px'>FR</th><td>" + cells[0] + "</td><td>" + cells[1] + "</td><td>" + cells[2] + "</td><td rowspan='3'width='150px'></td><td rowspan='3'>");
	append("<ul class='searchlist' id='searchlist'><li class='searchlist'><input type='text' class='searchlist' id='search' onkeypress=\"eval_form(event,'#searchbutton')\" maxlength='9'>");
	append("<button id='searchbutton' onclick='addCourse()'><img src='_images/magnifying_glass.png' height='10px'></button></li></ul></td></tr>");
	
	//add the other rows
	for (var x=3; x<numcells; x++){
		if (x % 3 == 0){
			append("<tr><th width='50px'>");
		}
		
		if (x == 3){
			append("SO</th>");	
		}
		else if (x == 6){
			append("JR</th>");
		}
		else if (x == 9){
			append("SR</th>");
		}
		else if (x > 9 && x % 3 == 0){
			append("SR+</th>");
		}
		
		if (x == numcells-1){
			append("<td valign='middle' align='center'>" + cells[x] + "</td>");
		}
		else{
			append("<td>" + cells[x] + "</td>");
		}
		
		if (x % 3 == 2 && x < (numcells - 3)){
			append("</tr>\n");
		}
	}
	
	//add the trashcan and close the table
	append("<td></td><td><ul class='trash' id='trash'><img src='_images/trash_full.jpg' height='150px'></ul></td></tr></table>");
		
	//appends the HTML in appendBuffer to the fp_wrapper <div>
	flushAppendBuffer();
	
	if ($fp.isNew){
		$('#populate').removeAttr('disabled');
		$fp.isNew = false;
	}
	
	var undoCount = 0, redoCount = 0;
	for (var num in plan.undoStack){
		undoCount++;	
	}
	
	for (var num in plan.redoStack){
		redoCount++;	
	}
	
	if (undoCount == 0) $('#undo').attr('disabled','disabled');
	if (redoCount == 0) $('#redo').attr('disabled','disabled');
	$('#search').val("");

	if (plan.studentWritePriv){
		enable();
	}
	else{
		disable();
	}
	setUpKeyboardShortcuts();
	
	for (course in $fp.courseObject){
		setTooltip($fp.courseObject[course].uniqueID);	
	}
}

//append(content)
//content is buffered because it must be written all at once
//see flushAppendBuffer() & populatePlan()
function append(content){
	appendBuffer += content;	
}

//flushAppendBuffer()
//content is buffered because it must be written all at once
//see append(content)
function flushAppendBuffer(){
	$('#fp_wrapper').append(appendBuffer);
	appendBuffer = "";
}
