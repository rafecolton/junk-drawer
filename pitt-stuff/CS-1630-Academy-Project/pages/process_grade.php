 <?
	require("../glue.php");
	init("form_process");

	$mail = new PHPMailer();

	if($_SESSION["usertype"] != "teacher" && $_SESSION["usertype"] != "admin")
	{
		error_message("Invalid permissions...");
		get_footer();
		die;
	}

	else{
		if (isset($_POST["assignment_id"]) && isset($_POST["user_id"]) && isset($_POST["gr1"]) 
			&& isset($_POST["gr2"]) && isset($_POST["gr3"]) && isset($_POST["gr4"])
			&& isset($_POST["gr5"]) && isset($_POST["gr6"]) && isset($_POST["gr7"])
			&& isset($_POST["gr8"]) && isset($_POST["comment"]))
		{
			$user_id = sqlite_escape_string($_POST['user_id']);
			$assignment_id = sqlite_escape_string($_POST['assignment_id']);
			$program_Execution = sqlite_escape_string($_POST['gr1']);
			$program_Specification = sqlite_escape_string($_POST['gr2']);
			$readability = sqlite_escape_string($_POST['gr3']);
			$reusability = sqlite_escape_string($_POST['gr4']);
			$documentation = sqlite_escape_string($_POST['gr5']);
			$accountable = sqlite_escape_string($_POST['gr6']);
			$lab_Report = sqlite_escape_string($_POST['gr7']);
			$timeliness = sqlite_escape_string($_POST['gr8']);
			$comment = sqlite_escape_string($_POST["comment"]);

			$grader_email = $_SESSION["email"];
			$grader_username = $_SESSION["username"];

			$Total = $program_Execution+$program_Specification+$readability+$reusability+$documentation+$accountable+$lab_Report+$timeliness;
			
			$results = $db->arrayQuery("select * from User where user_id = '".$user_id."'");
			if(empty($results))
			{
				echo("error");
				die;
			}

/**
FOR MAIL
**/
			$mail->AddAddress($results[0]["email"],$results[0]["username"]);
			$mail->AddReplyTo($grader_email, $grader_username);
/**
END FOR MAIL
**/
			$username = $results[0]["username"];
			$to = $username." <".$results[0]["email"].">";

			$results = $db->arrayQuery("select * from Assignment where assignment_id = '".$assignment_id."'");
			if(empty($results))
			{
				echo("error");
				die;
			}
			$title = $results[0]["title"];
			
			//check to see if user already has a grade in teh system
			$results = $db->arrayQuery("select * from grade where user_id = '".$user_id."' and assignment_id = '".$assignment_id."'");
			
			//no grade in system, so insert grade with comments
			if(empty($results))
			{
				$result = $db->queryExec("insert into grade values('$user_id', '$assignment_id', '$Total', '$comment')");

				$success = $db->changes();

				if(!$success)
				{
					echo("error");
					die;
				}
				else
				{
					echo("success");
				}
			}

			//grade has been inserted before so update
			else
			{

				$result = $db->queryExec("Update grade set grade = '$Total', comment = '$comment' where user_id = '$user_id' and assignment_id = '$assignment_id'");

				$success = $db->changes();

				if(!$success){
					echo("error");
					die;
				}
				else{
					echo("success");
				}
				
			}

			//attempt to send mail to student

			//$to holds Username <email@address.com>
			$subject = "Grade Submitted for ".$title;
			
			//headers for sending an HTML email
			/*$headers = 'MIME-Version: 1.0'."\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
			$headers .= "From: PSTA Homework Grading System <no-reply@cs.pitt.edu>"."\r\n";*/

			//$image_tag = (file_exists(LOGO_PATH)) ? "<img src='".LOGO_PATH."'>" : "";
			$image_tag = "<img src='cid:1000' height=75 width=75>";

			$message = "
			<html>
				<body>
				<table style='border-spacing: 0px; border-collapse: collapse;'>
					<tr style='width: 601px; height: 100px; background-color: #414A6C; color: white; padding: 10px; border-right: 1px solid #414A6C;'>
							<td style='padding: 10px'><div style='font-size: 30px; padding-top: 15px; padding-left: 15px;'>Pittsburgh Science and Technology Academy</div><br><div style='font-size: 18px; padding-left: 15px'>Homework Grading and Submission System</div></td>
							<td style='padding: 10px'><div style='width: 75px; height: 75px; '>$image_tag</div></td>
					</tr>
					<tr style='border-left: 1px solid black; border-right: 1px solid black;'>
						<td style='padding-left: 10px; padding-top: 25px; padding-bottom; 25px;'>
							<div style='padding-left: 10px'>
								<br><br>
								<div style='font-size: 16px'>$username, your submission for $title has been graded.</div><br>
								<br>
								<b><u>Breakdown of Score</u></b><br>
								<br>
								<table border='0' style='border-spacing: 0px; border-collapse: collapse;'>
								<tr>
									<td style='width: 250px;'><b>Program Execution:</b></td>
									<td>$program_Execution</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Program Specification:</b></td>
									<td>$program_Specification</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Readability:</b></td>
									<td>$readability</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Reusability:</b></td>
									<td>$reusability</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Documentation:</b></td>
									<td>$documentation</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Use of Class Time:</b></td>
									<td>$accountable</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Lab Report:</b></td>
									<td>$lab_Report</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Timeliness:</b></td>
									<td>$timeliness</td>
								</tr>
								<tr>
									<td style='width: 250px; vertical-align: top;'><b>Comments:</b></td>
									<td>$comment</td>
								</tr>
								<tr>
									<td style='width: 250px;'><b>Total:</b></td>
									<td>$Total/70</td>
								</tr>
								</table>
								<br><br>
							</div>
						</td>
						<td><div></div></td>
					</tr>
					<tr style='background-color: #495caf; color: #495caf; width: 601px; margin: 0 auto; height: 30px; line-height: 30px; border-right: 1px solid #495caf'><td style='height: 30px; color: #495caf;'>footer</td><td style='height: 30px;'></td></tr>	
				</table>
				</body>
			</html>
			";

			$mail->IsSMTP();
			$mail->From = "no-reply@cs.pitt.edu";
			$mail->FromName = "PSTA Homework Grading System";
			$mail->IsHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $message;
			$mail->AddEmbeddedImage(LOGO_PATH, 1000, "PSTALogo.png");
			if(!$mail->Send())
			{
				echo "error";
				die;
			}
			echo "success";
		}	

	}

	?>