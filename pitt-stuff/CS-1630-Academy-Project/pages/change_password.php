<?
	require("../glue.php");
	init("page");
	//enqueue_script($filename)
	get_header();
?>

<h1>Change Password</h1>
<form method='post' id='password_form'>
	<table>
		<tr>
			<td>Old Password:</td><td><input type='password' name='old_password' id='old_password' onkeypress="eval_form(event,'#submitbutton')"></td>
		</tr>
		<tr>
			<td>New Password:</td><td><input type='password' name='new_password' id='new_password' onkeypress="eval_form(event,'#submitbutton')"></td>
		</tr>
		<tr>
			<td>Confirm New Password:</td><td><input type='password' name='new_password_2' id='new_password_2' onkeypress="eval_form(event,'#submitbutton')"></td>
		</tr>
	</table>
	<input type='button' id='submitbutton' value='Submit' onclick='submit_form()'>&nbsp;<input type='reset' value='Reset' id='reset_button'><? add_token(); ?>
</form>
<div id='loading-bar' style="display: none; margin-top: -17.5px; margin-left: 120px;"><img src="<?= HOME_DIR ?>images/loading.gif"></div><br>
<div class='message-wrapper' style='display: none;' id='success_message'><div class='info message'>Password Changed<br></div></div>
<div class='message-wrapper' style='display: none;' id='failure_message'><div id='failure_message_data' class='warning message'></div></div>

<script>

	function submit_form(){
		if ($('#old_password').val() == "" || $('#new_password').val() == "" || $('#new_password_2').val() == ""){
			alert("All fields required");
		}
		else{
			$("#loading-bar").css("display","block");
			var data = $('#password_form').serialize();

			post("process_change.php",data,function(result){
				$('#reset_button').click();

				if (result.length < 50 && result.indexOf("Success") != -1){
					$('#loading-bar').css("display","none");
					if (!(typeof t == "undefined")) clearTimeout(t);
					$('#success_message').show("slow");
					t = setTimeout(function(){
						$('#success_message').hide("slow");
						$('#success_message').css("display","none");
					},3500);
				}
				else if (result.length < 50){
					$('#loading-bar').css("display","none");
					if (!(typeof t == "undefined")) clearTimeout(t);
					$('#failure_message_data').html(result + "<br>");
					$('#failure_message').show("slow");
					t = setTimeout(function(){
						$('#failure_message').hide("slow");
						$('#failure_message').css("display","none");
						$('#failure_message_data').html("");
					},3500);
				}
				else{
					$('#loading-bar').css("display","none");
					if (!(typeof t == "undefined")) clearTimeout(t);
					$('#failure_message_data').html("error<br>");
					$('#failure_message').show("slow");
					t = setTimeout(function(){
						$('#failure_message').hide("slow");
						$('#failure_message').css("display","none");
						$('#failure_message_data').html("");
					},3500);
				}
			});
		}
	}

	$(document).ready(function(){
		$('.message-wrapper').click(function(){
			$(this).hide("slow");
		});
	})

</script>

<? get_footer(); ?>