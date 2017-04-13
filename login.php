<?php 
/**************************************************
	Login page

	If the user didn't supply the right login info, show them to a login form.
	Also, display $message to them.
*/
function printLoginPage($message) {
	echo ' <!DOCTYPE html>
		<html> 
		<head> 
			<link rel="stylesheet" type="text/css" href="css/main.css" /> 
			<title>voipSMS: Login failed</title> 
		</head> 
		<body>';
		require_once('header.php'); 
	echo $message;
	echo '</body> 
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
if($_SERVER['REQUEST_METHOD'] != "POST") {
	printLoginPage("Error: No login information supplied.");
} else {
	// Also, test if they supplied the right login info
	if(isset($_POST['vms_email'])
	&& isset($_POST['userPassword'])) { 
		// User supplied all the right info; Attemt to log them in
		loginUser();
	} else {
			printLoginPage("Error: Not all login information supplied.");
	}
}
?>
