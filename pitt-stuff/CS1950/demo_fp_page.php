<?php
require_once("functions.php");
load_base();
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Demo Future Planning Page</title>
<?php print_links() ?>
<script>
	//here is some random comment
	function addCourse(){		
		var field = $('#search').val();
		if (!(field == "") && $('#searchlist li').size() < 16){
			$('#search').val("");
			$("#searchlist").append("<li class='course' id = '" + field +"'>" + field + "</li>");
		}
	}

	$(function(){
		$("ul.semester.normal, ul.semester.summer").sortable({
			connectWith: 	"ul.semester, ul.trash",
			cursor:			"move"
		});

		$("#searchlist, #trash").sortable({
			connectWith: 	"ul",
			cursor:			"move"
		});
		
		$("ul").sortable({
			receive: function(event, ui) {
				if (!undoInProgress && !redoInProgress){
					actionObj = new Object();
					actionObj.from = ui.sender.attr('id');
					actionObj.to = $(this).closest('ul').attr('id');
					actionObj.item = ui.item;	
					undoStack.push(actionObj);	
					$('#undo').removeAttr('disabled');	
					
					if (actionObj.from == 'searchlist') $(actionObj.item).toggleClass('ok',true);
				}				
			}
		});
	});
	
	function undo(){
		$('#redo').removeAttr('disabled');
		
		undoInProgress = true;
		actionObj = undoStack.pop();
		
		var from = '#' + actionObj.from;
		var to = '#' + actionObj.to;
		var item = actionObj.item;
		
		$(from).append(item);
		if (from == "#searchlist") $(item).toggleClass('ok',false); 
		else $(item).toggleClass('ok',true);
		
		redoStack.push(actionObj);
		undoInProgress = false; 
		
		if (undoStack.length == 0) $('#undo').attr('disabled','disabled');
	}
	
	function redo(){
		$('#undo').removeAttr('disabled');
		
		redoInProgress = true;
		actionObj = redoStack.pop();
				
		var from = '#' + actionObj.from;
		var to = '#' + actionObj.to;
		var item = actionObj.item;
		
		$(to).append(item);
		if (to == "#searchlist") $(item).toggleClass('ok',false);
		else $(item).toggleClass('ok',true);
		
		undoStack.push(actionObj);
		redoInProgress = false;
		
		if (redoStack.length == 0) $('#redo').attr('disabled','disabled');
	}
	
</script>
</head>

<body>
<script>
	var undoInProgress = false, redoInProgress = false;
	var undoStack = new Array();
	var redoStack = new Array();
</script>
<div id="fp_wrapper">

<button id='undo' onclick='undo()'>Undo</button>&nbsp;<button id='redo' onclick='redo()'>Redo</button>

<?php

#preparing $cells, which is an array that contains all of the <ul> elements in order with proper formatting


$numcells=12;

for ($semester=0; $semester<12; $semester++):

	$cells[$semester] = "<ul id='semester$semester'";
	
	if ($semester % 3 == 2 && $semester != 11):
		$cells[$semester] .= " class='semester summer'";
	elseif ($semester == 11):
		$cells[$semester] .= " class='semester none'";
	else:
		$cells[$semester] .= " class='semester normal'";
	endif;
	
	if ($semester != 11):
		$cells[$semester] .= "></ul>";
	else:
		$cells[$semester] .= "><span class='new_plus'>+</span></ul>";
	endif;
endfor;
?>

<!--table headers-->
<table id='planTable' border='0'>
<tr>
	<th></th>
	<th>Fall</th>
	<th>Spring</th>
	<th>Summer</th>
</tr>

<tr><?php

#echos the first row manually so the serachlist can be inserted
#then echos the the other rows with row headers & the cells from the $cells array
#then echos the trash <ul>
#finally, some JavaScript in the body

echo "<th width='50px'>FR</th><td>$cells[0]</td>";
echo "<td>$cells[1]</td>";
echo "<td>$cells[2]</td>";
?>

<td rowspan='3'width='150px'></td>
<td rowspan='3'>
<ul class='searchlist' id='searchlist'>
	<li class="searchlist">
		<input type="text" class = "searchlist" id="search" onkeypress="eval_form(event,'#searchbutton')" maxlength="9">
		<button id="searchbutton" onclick="addCourse()"><img src="_images/magnifying_glass.png" height="10px"></button>
	</li>
</ul>
</td></tr>

<?php
for ($num=3; $num<$numcells; $num++):
	if ($num % 3 == 0) echo "<tr><th width='50px'>";
	
	if ($num == 3):
		echo "SO</th>";
	elseif ($num == 6):
		echo "JR</th>";
	elseif ($num == 9):
		echo "SR</th>";
	elseif ($num > 9 && $num % 3 == 0):
		echo "SR+</th>";
	endif;
	
	if ($num == $numcells-1):
		echo "<td valign='middle' align='center'>$cells[$num]</td>";
	else:
		echo "<td>$cells[$num]</td>";
	endif;
	
	if ($num % 3 == 2 && $num < ($numcells-3)) echo "</tr>\n";

endfor;
?>

<td></td><td><ul class="trash" id="trash"><img src="_images/trash_full.jpg" height="150px"></ul></td></tr>
</table>

</div>

<script>
$('#undo').attr('disabled','disabled');
$('#redo').attr('disabled','disabled');
$('#search').val("");

var controlCommandPressed = false;
var shiftPressed = false; 

setUpKeyboardShortcuts();

</script>
</body>
</html>