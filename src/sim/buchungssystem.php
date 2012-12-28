<?php
	require '../lib/DBAccess.php';	
	require '../domain/AccessEntry.php';
	
	session_start();
	$dbh = new DBAccess();
	

	$action = $_GET["action"];
	if(empty($action))
		$action = "form";
		
	switch($action) {
		case "form":
			$return = form($dbh);
			break;
		case "save":
			$return = save();
			break;
	}
	
	echo $return["content"];
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title>Buchungssystem</title>
		<link rel="stylesheet" href="../jquery/jquery.ui.all.css">
		<link rel="stylesheet" tyep="text/css" href="../web.css" />
		<script src="../jquery/jquery-1.8.3.min.js"></script>
		<script src="../jquery/jquery.ui.core.js"></script>
		<script src="../jquery/jquery.ui.slider.js"></script>
		<script src="../jquery/jquery.ui.widget.js"></script>
		<script src="../jquery/jquery.ui.datepicker.js"></script>
		<script src="../jquery/jquery-ui-timepicker-addon.js"></script>
		<script>
			$(function() {
				$.timepicker.regional['de'] = {
				  timeOnlyTitle: 'Uhrzeit auswählen',
				  timeText: 'Zeit',
				  hourText: 'Stunde',
				  minuteText: 'Minute',
				  secondText: 'Sekunde',
				  currentText: 'Jetzt',
				  closeText: 'Auswählen',
				  ampm: false
				};
				$.timepicker.setDefaults($.timepicker.regional['de']);
				
				$('#from').datetimepicker({
					changeMonth: true,
					numberOfMonths: 1,
					dateFormat: "yy-mm-dd",
					onClose: function( selectedDate ) {
						$( "#to" ).datepicker( "option", "minDate", selectedDate );
					}
				});
				$('#to').datetimepicker({
					changeMonth: true,
					numberOfMonths: 1,
					dateFormat: "yy-mm-dd",
					onClose: function( selectedDate ) {
						$( "#from" ).datepicker( "option", "maxDate", selectedDate );
					}
				});
			});
			$(document).ready(function() {
				$('#randVals').click( function() {
					$.ajax({
						url: "randomData.php",
						type: "GET",
					
						success: function (reqCode) {
							var fields = reqCode.split("|");
							$('#name').val(fields[0]);
							$('#from').val(fields[1]);
							$('#to').val(fields[2]);
							$('#location option[value=' + fields[3] + ']').attr('selected',true);
						}
					});
				});
			});
		</script>
		<?php echo $return["head"]; ?>
	</head>
	
	<body>
		<?php
			
			//-----------------------------------------------------
			
			function form($dbh) {
				
				$content = '
				<form action="?action=save" method="post">
					<table border="0" cellspacing="0" cellpadding="4">
						<tr>
							<td>name</td>
							<td><input type="text" name="name" id="name" size="35" /></td>
						</tr>
						<tr>
							<td>location</td>
							<td>
								<select name="location" id="location">';
									$result = $dbh->query("SELECT LockId, Location FROM `lock` ORDER BY Location");
									while ($row = $result->fetchObject()) {
										$content .= '
									<option value="'.$row->LockId.'">'.$row->Location.'</option>';
									}
									$content .= '
								</select>
							</td>
						</tr>
						<tr>
							<td>begin</td>
							<td><input type="text" name="begin" id="from" /></td>
						</tr>
						<tr>
							<td>end</td>
							<td><input type="text" name="end" id="to" /></td>
						</tr>
						<!--<tr>
							<td>id</td>
							<td><input type="text" name="id" /></td>
						</tr>-->
					</table><br/>
					<input type="submit" value="save">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" id="randVals" value="random" />
					<div id="debug"></div>
				</from>';
				$return["content"] = $content;
				return $return;
			}
			
			function save() {								
				//regex to check date time string
				$pattern = '/^([\d]{4})[-](0[1-9]|1[0-2])[-](0[1-9]|1[0-9]|2[0-9]|3[0-1])[ ](0[0-9]|1[0-9]|2[0-3])[:]([0-5][0-9])/';
				
				if((1 == preg_match($pattern, $_POST["begin"])) && (1 == preg_match($pattern, $_POST["end"]))) {
					$return["content"] =  '<div class="succ" >Benutzer erfolgreich eingetragen</div>';
							
				
					$nameArray = explode(" ", $_POST["name"]);
					
					if(count($_SESSION['accessEntryList']) == 0) //dummy eintrag erzeugen
						$_SESSION['accessEntryList'][0] = new AccessEntry();

					$_SESSION['accessEntryList'][] = new AccessEntry(count($_SESSION['accessEntryList']), $nameArray[0], $nameArray[1], $_POST["location"], $_POST["begin"], $_POST["end"]);
				}	
				else
					$return["content"] =  '<div class="err" >Benutzer konnte nicht angelegt werden!</div>';
				$return["head"] = '<meta http-equiv="refresh" content="1;url=?action=form">';
				return $return;
			}
		?>
		<div id="debug"></div>
	</body>
</html>