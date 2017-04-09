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
	require_once("sql/dbinfo.php");
	$db = connectToDB();

	// Validating user login against db
	$stmt = $db->prepare("SELECT userID, vms_email, vms_apiPassword, name
		FROM users WHERE
		vms_email=:vms_email AND userPassword=SHA2(:userPassword,256)");

	$stmt->bindValue(":vms_email", trim($_POST['vms_email']));
	$stmt->bindValue(":userPassword", trim($_POST['userPassword']));
	$stmt->execute();
	
	// Checking if we've got a match
	if($stmt->rowCount() == 1) {
		// Saving the user's info in a session variable
		$_SESSION['auth'] = TRUE;

		// Copying the user's db info to the session variable
		// $_SESSION['auth'] => True
		// $_SESSION['auth_info'] => 
		//		['userID']	
		//		['vms_email']	
		//		['vms_apiPassword'] (base64 encoded)
		//		['name']
		$userData = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$_SESSION['auth_info'] = $userData[0];
		
		// Head back home
		header("Location: index.php");
		return;
	} else {
		// Login failed. Tell the user
		printLoginPage("The login information supplied is not valid.");
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
