<?php
session_start();
require_once('sql/dbQueries.php');

/**************************************************
	Print add contact form
*/
function printAddContactForm() {
	echo "<div class='formWrapper'>";
	echo "<h3>Add a contact</h3>";
	echo '<form action="addContact.php" method="POST" ';
	echo 'name="addContact" onsubmit="return validateAddContact()">';
		echo '<label>First name</label>';
		echo '<input name="firstName" />';

		echo '<label>Last name</label>';
		echo '<input name="lastName" />';

		echo '<label>Phone number</label>';
		echo '<input name="did" type="number" />';

		echo '<label>Notes</label>';
		echo '<textarea name="notes">';
		echo '</textarea>';

		echo '<input type="submit">';
	echo '</form>';
	echo "</div>";
}

/**************************************************
	Create contact
*/
function createContact() {
	// -- Validating contact info --
	$validated = True;
	$errors = array();

	// Making sure everything's set
	if(isset($_POST['firstName']) &&
	isset($_POST['lastName']) &&
	isset($_POST['did'])) {
	    // Begin testing the actual contents of the form

		// Since notes isn't a required field, make sure it's set.
		if(!isset($_POST['notes']))
			$notes = "";
		else 
			$notes = $_POST['notes'];
		
	} else {
		$validated = False;
		$errors[] = "Form wasn't completely filled out.";
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
		
		// Also, print the add contact form again so they can try again.
		printAddContactForm();                                         
		return;                                                          
	}                                                                    


	// -- Adding user to db --
	addContact($_SESSION['auth_info']['userID'],
		$_POST['firstName'], 
		$_POST['lastName'], 
		$_POST['did'], 
		$notes);

	echo "<div class='message'>Added contact successfully.";
	echo "<a href='contactList.php'>Back to contact list</a></div>";
	printAddContactForm();                                         
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"> 
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<title>voipSMS</title>
	
<script>
function validateDID(did) {
	if(/\d{10}/.test(did))
		return (true)
	
	return (false)
}

// Make sure form is filled completely and such.
function validateAddContact() {
	var errors = [];
	var form = document.forms['addContact'];
	var errorMessage = document.getElementById('formErrorMessage');
	
	// Clear error classes from inputs
	form['firstName'].classList.remove("formError");
	form['lastName'].classList.remove("formError");
	form['did'].classList.remove("formError");
	errorMessage.classList.remove('error');
	
	// Clear the error div
	errorMessage.innerHTML = "";
	
	// -- Begin processing form --
	// Making sure values aren't empty
	if(form['firstName'].value == "") {
		errors.push("First name cannot be empty.");
		form['firstName'].classList.add('formError');
	}
	
	if(form['lastName'].value == "") {
		errors.push("Last name cannot be empty.");
		form['lastName'].classList.add('formError');
	}
	
	if(form['did'].value == "") {
		errors.push("Contact phone number cannot be empty.");
		form['did'].classList.add('formError');
	}

	// Making sure the contact DID is in the right format (dddddddddd)
	if(!validateDID(form['did'].value)) {
		errors.push("Contact phone number is not in the right format. Example format: '1231231234'");
		form['did'].classList.add('formError');
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
	include_once("header.php");
	echo "<div id='formErrorMessage'></div>";

	// Make sure the user is actually logged in
	if(!isset($_SESSION['auth'])) {
		echo "<div id='error'>Error: You must be signed in to visit this page!</div>";
	} else {
		// If the form was submitted, attempt to create a contact
		// Otherwise, print the contact form
		if($_SERVER['REQUEST_METHOD'] === "POST") {
			createContact();
		} else {
			printAddContactForm();
		}
	}
?>
</body>
</html>
