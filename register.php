<?php
session_start();

/**************************************************
	Print registration form
	
	- Prints the html form for adding a user
*/
function printRegistrationForm() {
	// Let the user know that they need to enable API access on their
	//		voip.ms account before registering.
	echo '<div class="warning">
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

	echo '<div class="formWrapper">';
	echo ' <form name="register" action="register.php" method="POST"';
	echo '	onsubmit="return validateRegister()">';
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
	echo "</div>";
}

/**************************************************
	Create User
	
	Validate user input, add to db, and redirect to home page
*/
function createUser() {
	require_once('sql/dbinfo.php');
	require_once('sql/dbQueries.php');
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
		$validated = False;
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
		echo "Added user successfully. <br />";

		// -- Adding user's DIDs to the db --
		// We need the user's user ID first though.	
		$user = getUserFromLogin($_POST['vms_email'], $_POST['password']);
		if($user == False) {
			echo "Unable to add DIDs; User not found in DB.";
			return;
		}

		syncUserDIDs($user[0]['userID']);
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="ISO-8859-1">
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>voipSMS: Register</title>

<script>
// Source: http://www.w3resource.com/javascript/form/email-validation.php
function validateEmail(mail) {  
	if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))  
		return (true)  

	return (false)  
}  

// Make sure form is filled completely and such.
function validateRegister() {
	var errors = [];
	var form = document.forms['register'];
	var errorMessage = document.getElementById('formErrorMessage');

	// Clear error classes from inputs
	form['name'].classList.remove("formError");
	form['password'].classList.remove("formError");
	form['password2'].classList.remove("formError");
	form['vms_email'].classList.remove("formError");
	form['vms_apiPassword'].classList.remove("formError");
	errorMessage.classList.remove('error');

	// Clear the error div
	errorMessage.innerHTML = "";

	// -- Begin processing form --
	// Making sure values aren't empty
	if(form['name'].value == "") {
		errors.push("Name cannot be empty.");
		form['name'].classList.add('formError');
	}

	if(form['password'].value == "") {
		errors.push("Password cannot be empty.");
		form['password'].classList.add('formError');
	}

	if(form['vms_email'].value == "") {
		errors.push("VoIP.ms email cannot be empty.");
		form['vms_email'].classList.add('formError');
	}

	if(form['vms_apiPassword'].value == "") {
		errors.push("VoIP.ms API password cannot be empty.");
		form['vms_apiPassword'].classList.add('formError');
	}

	// Making sure passwords match
	if(form['password'].value != form['password2'].value) {
		errors.push("Passwords don't match.");
		form['password'].classList.add('formError');
		form['password2'].classList.add('formError');
	}

	// Making sure values are long enough
	if(form['password'].value.length < 8) {
		errors.push("Password isn't long enough (Min 8 characters).");
		form['password'].classList.add('formError');
	}

	if(form['name'].value.length < 2) {
		errors.push("Name isn't long enough (Min 2 characters).");
		form['name'].classList.add('formError');
	}

	// Making sure vms Email is valid
	if(!validateEmail(form['vms_email'].value)) {
		errors.push("VoIP.ms email isn't valid.");
		form['vms_email'].classList.add('formError');
	}


	// -- Writing errors --
	var numErrors = errors.length;
	if(numErrors > 0) {
		// Loop though errors and write them to the error message div
		errorMessage.innerHTML = "Errors found while processing the form:";

		for(var i = 0; i < numErrors; i++) {
			errorMessage.innerHTML += "<br />";
			errorMessage.innerHTML += errors[i];
		}

		errorMessage.classList.add('error');
		return false;
	}
	
	return true;
}
</script>

</head>
<body>
<?php 
	require_once('header.php');
	echo "<div id='formErrorMessage'></div>";

	// If the form was submitted, attempt to create a user.
	// Otherwise, print the registration form 
	if($_SERVER['REQUEST_METHOD'] === "POST") {
		createUser();		
	} else {
		// Make sure that the user's not already logged in.
		if(isset($_SESSION['auth'])) {
			echo "<div class='error'>" .
				"Error: You can't register a user while you're logged in.</div>";
		}  else {
			printRegistrationForm();
		}
	}
?>
</body>
</html>
