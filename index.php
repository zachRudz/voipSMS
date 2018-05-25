<?php
session_start();
require_once("sql/dbQueries.php");

/**************************************************
	Write Logged Out Message

	Let the user know about voipSMS.
	Link them to the register page.
*/
function writeLoggedOutMessage() {
	echo "<div class='information'>";
	echo '<p>
		<a href="http://voip.ms/">voip.ms</a> offers a great API to compliment their VOIP service.
		In addition, they also offer an SMS service, which also fully interfaces with their API. 

		However, their desktop SMS center is lacking. For example...
	</p>
		<ul>
			<li>You must manually refresh if you want to check for new incoming SMS messages.</li>
			<li>They offer no support for a contacts list. You must remember phone numbers manually, or look them up through other means.</li>
			<li>The canon voip.ms SMS center is bland and unintuitive.</li>
		</ul>

	<p>
		voipSMS is an interface to the voip.ms API. 
		You can send and recieve SMS (text) messages using voipSMS, with a cleaner, more functional interface than voip.ms\' built-in SMS center.
	</p>
	<p>
		<a href="register.php">Register</a> today!
	</p> ';
	echo "</div>";
}

/**************************************************
	Write Logged In Message

	
*/
function writeLoggedInMessage() {
	echo "<div class='information'>";
	echo '<p>
		Welcome back, friendo.
	</p>
	<h3>Quick links</h3>
	<ul>
		<li><a href="contactList.php">Contact List</a></li>';
	
	// Link to admin page if we're an admin
	$user = getUser($_SESSION['auth_info']['userID']);
	if($user['userType'] == 'A')
		echo "<li><a href='admin.php'>Admin page</a></li>";

	echo '</ul>
	<h3>VoIP.ms links</h3>
	<ul>
		<li><a href="http://voip.ms">voip.ms</a></li>
	</ul>';
	echo "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<title>voipSMS</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>
<body>
<?php 
	include_once("header.php");

	// If the user is logged out, write the greeting message
	// Otherwise, write the SMS center in the body of the page
	if(!isset($_SESSION['auth'])) {
		writeLoggedOutMessage();
	} else {
		writeLoggedInMessage();
	}
?>
</body>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>
