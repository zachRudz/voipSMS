<!DOCTYPE html>
<html>
<head> <title>voipSMS</title>
</head>
<body>
<?php
	include_once("vms_api.php");

	// Make dat sms call
	if(isset($_POST['username'])) {
		$oldDate="2017-04-03";
		$newDate="2017-04-04";
		$contact="7097709013";
		$limit="50";
		$smsHistory = getSMS(
			$_POST['username'], 
			$_POST['password'], 
			$oldDate,
			$newDate,
			$contact,
			$limit);
	}
?>
<pre>
	<?php
	if(isset($smsHistory)) {
		print_r($smsHistory); 
	}
	?>
</pre>

<a href="index.php">Back home</a>
</body>
</html>
