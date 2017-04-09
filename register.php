<?php
/**************************************************
	Print registration form
	
	- Prints the html form for adding a user
*/
function printRegistrationForm() {
	// Let the user know that they need to enable API access on their
	//		voip.ms account before registering.
	echo '<div>
		Note: The VoIP.ms API is not enabled by default. You must enable API access
		on your VoIP.ms account before registering. You can allow the 
		API Access from within your account, by following these steps:

		<ol>		
			<li>Log-in to your VoIP.ms account</li>
			<li>Go to "Main Menu" -> "SOAP & REST/JSON API"</li>
		    <li>Click on button [Enable/Disable API] to Enable / Disable 
				the API Access</li>
		</ol>
	</div>';

	echo ' <form action="register.php" method="POST">';
	echo '<h3>voipSMS Account Information</h3>';
	echo '<label>Name </label>';
	echo '<input name="name" />';

	echo '<label>Password </label>';
	echo '<input type="password" name="password" />';

	echo '<label>Confirm password </label>';
	echo '<input type="password" name="password2" />';

	echo '<h3>voip.ms Account Information</h3>';
	echo '<label>voip.ms Email </label>';
	echo '<input name="vms_email" />';

	echo '<label>voip.ms API Password </label>';
	echo '<input type="password" name="vms_apiPassword" />';

	echo '<input type="submit">';
	echo '</form> ';
}

/**************************************************
	Create User
	
	Validate user input, add to db, and redirect to home page
*/
function createUser() {
	require_once('sql/dbinfo.php');
	require_once('vms_api.php');
	$db = connectToDB();

	// -- Validating user info --
	$validated = True;
	$errors = array();

	// Making sure everything's set
	if(isset($_POST['name']) &&
	isset($_POST['password']) &&
	isset($_POST['password2']) &&
	isset($_POST['vms_email']) &&
	isset($_POST['vms_apiPassword'])) { 
		// Begin testing the actual contents of the form

		// Making sure that the email isn't a duplicate
		$select_stmt = $db->prepare("SELECT * FROM `users` WHERE vms_email = :vms_email");
		$select_stmt->bindValue(":vms_email", trim($_POST['vms_email']));
		$select_stmt->execute();

		if($select_stmt->rowCount() != 0) {
			$validated = False;
			$errors[] = "A user with this email already exists.";
		} 

		// Testing if the username is long enough
		if(strlen(trim($_POST['name'])) < 2) {
			$validated = False;
			$errors[] = "Name is too short (min 3 characters).";
		}

		// Testing if the passwords match
		if($_POST['password'] != $_POST['password2']) {
			$errors[] = "Passwords don't match.";
		}

		// Testing if the password is long enough
		if(strlen(trim($_POST['password'])) < 8) {
			$validated = False;
			$errors[] = "Password is too short (min 8 characters).";
		}

		// Testing if the email passes email validation
		if(!filter_var($_POST['vms_email'], FILTER_VALIDATE_EMAIL)) {
			$validated = False;
			$errors[] = "Email is not valid.";
		}
		
		// Testing if the vms api password/email combo works
		// Returns $results['status'] => Success if valid
		$vmsCredentialStatus = validateLogin($_POST['vms_email'],
			$_POST['vms_apiPassword']);

		if(trim($vmsCredentialStatus['status']) != trim("success")) {
			$validated = False;
			$errors[] = "voip.ms credentials failed to validate (Reason: " . 
				$vmsCredentialStatus['status'] . ").";
		}
	} else {
		// Not all of the form elements are there.
		$errors[] = "Form wasn't completely filled out";
	}
	
	// Testing if validation failed or not
	if(!$validated) {
		// Validation failed, let the user know.
		echo "<div>User form validation failed:</div>";
		echo '<ul class="errors">';
		foreach($errors as $e) {
			echo "<li>" . $e . "</li>";
		}
		echo '</ul>';

		// Also, print the registration form again so they can try again.
		printRegistrationForm();
		return;
	}
	
	// -- Adding user to db --
	$add_stmt = $db->prepare("INSERT INTO `users` (`vms_email`, `vms_apiPassword`, `userPassword`, `name`) VALUES (:vms_email, :vms_apiPassword, SHA2(:userPassword,256), :name)");
	
	$add_stmt->bindValue(":vms_email", trim($_POST['vms_email']));
	$add_stmt->bindValue(":vms_apiPassword", base64_encode(trim($_POST['vms_apiPassword'])));
	$add_stmt->bindValue(":userPassword", trim($_POST['password']));
	$add_stmt->bindValue(":name", trim($_POST['name']));
	$add_stmt->execute();
	
	if($add_stmt->rowCount() != 1) {
		$errors[] = "There was a problem saving your data to the database. Please try again later.";

		// Validation failed, let the user know.
		echo "<div>User form validation failed:</div>";
		echo '<ul class="errors">';
		foreach($errors as $e) {
			echo "<li>" . $e . "</li>";
		}
		echo '</ul>';
	} else {
		echo "Added user successfully.";
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>voipSMS: Register</title>
</head>
<body>
<?php 
	session_start();
	require_once('header.php');

	// If the form was submitted, attempt to create a user.
	// Otherwise, print the registration form 
	if($_SERVER['REQUEST_METHOD'] === "POST") {
		createUser();		
	} else {
		// Make sure that the user's not already logged in.
		if(isset($_SESSION['auth'])) {
			echo "<div id='error'>" .
				"Error: You can't register a user while you're logged in.</div>";
		}  else {
			printRegistrationForm();
		}
	}
?>
</body>
</html>
