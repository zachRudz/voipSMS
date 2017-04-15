<?php
session_start();
require_once('sql/dbQueries.php');

/**************************************************
	Print add contact form
*/
function printAddContactForm() {
	echo '<form action="addContact.php" method="POST">';
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

	echo "Added contact successfully. <a href='contactList.php'>Back to contact list</a>";
	printAddContactForm();                                         
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<title>voipSMS</title>
</head>
<body>
<?php 
	include_once("header.php");

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
