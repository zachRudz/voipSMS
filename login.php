<?php 
/**************************************************
	Login page

	If the user didn't supply the right login info, show them to a login form.
	Also, display $message to them.
*/
function printLoginPage($message) {
	echo '<!DOCTYPE html>
		<html> 
		<head> 
		    <meta charset="utf-8">
			<title>voipSMS: Login</title> 
			<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		</head> 
		<body class="text-center">';
	include_once('header.php'); 

	// Login form
	echo '
	<form class="container" action="login.php" method="POST">
		<h1 class="h3 my-3 font-weight-normal">Sign in</h1>
		<div class="form-group">
			<label for="emailInput">Email Address</label>
			<input type="email" class="form-control" id="emailInput" aria-describedby="emailHelp" placeholder="Enter email" name="vms_email" required />
		</div>
	
		<div class="form-group">
			<label for="passwordInput">Password</label>
			<input type="password" class="form-control" id="passwordInput" aria-describedby="passwordHelp" placeholder="Password" name="userPassword" required />
		</div>

		<button type="submit" class="btn btn-primary">Submit</button>
	</form> ';

	// Errors, if any
	if($message != "") {
		echo "<div class='alert alert-danger'><strong>Error:</strong> {$message}</div>";
	}

	echo '</body> 
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		</html>';
}

function loginUser() {
	session_start();
	require_once("sql/dbQueries.php");

	// Fetching user data from login data
	$userData = getUserFromLogin($_POST['vms_email'], $_POST['userPassword']);

	if($userData == False) {
		printLoginPage("The login information supplied is not valid.");
		return;
	} else {
		// Saving the user's info in a session variable
		$_SESSION['auth'] = TRUE;
		$_SESSION['auth_info'] = $userData[0];

		// -- Setting the user's active DID --
		// Getting the user's DIDs
		$dids = getDIDs($_SESSION['auth_info']['userID']);
		if(count($dids) == 0) {
			$_SESSION['auth_info']['activeDID'] = "No user DID selected.";
		} else {
			$_SESSION['auth_info']['activeDID'] = $dids[0]['did'];
		}

		// Head back home
		header("Location: index.php");
		return;
	}
}

/**************************************************
	Entry point

	$_POST:
		Attempt to login the user.
		Validate their info, and compare to DB.

		If valid, set their session variables.

	Else:	
		Show them an error message.
*/
if($_SERVER['REQUEST_METHOD'] == "GET") {
	// User got here via GET. Display the login form.
	printLoginPage();
} else {
	// Also, test if they supplied the right login info
	if(isset($_POST['vms_email']) && isset($_POST['userPassword'])) { 
		// User supplied all the right info; Attemt to log them in
		loginUser();

	} else {
		printLoginPage("Not all login information supplied.");
	}
}
?>
