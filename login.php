<?php 
/**************************************************
	Login page

	If the user didn't supply the right login info, show them to a login form.
	Also, display $message to them in the form of an error.
*/
function printLoginPage($message = null) {
	require_once("pageTop.php");
	echo '	<title>voipSMS: Login</title> 
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

	echo '</body>';
	require_once("pageBottom.php");
}


// Attempt to login the user
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

		// Fetching the DID for the user's default DID.
		$activeDID = getDIDFromID($userData[0]['didID_default']);
		if($activeDID != false) {
			$_SESSION['auth_info']['activeDID'] = $activeDID['did'];
		} else {
			$_SESSION['auth_info']['activeDID'] = null;
		}
		//print_r($_SESSION);

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
