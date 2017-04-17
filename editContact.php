<?php 
session_start();
require_once("sql/dbQueries.php");

function displayEditContactForm($contactID) {
	// Getting the contact
	$contact = getContact($_SESSION['auth_info']['userID'], $contactID);

	// Making sure things are k (Found the contact, and it belongs to $userID)
	if($contact['status'] == "not_found") {
		echo "<div class='error'>Error: No contact found with that ID.</div>";
		return;
	} else if($contact['status'] == "not_owner") {
		echo "<div class='error'>Error: This contact does not belong to you!</div>";
		return;
	} else if($contact['status'] != "success") {
		echo "<div class='error'>Unexpected error: Unable to get contact.</div>";
		return;
	}

	echo "<div class='formWrapper'>";
	echo "	<form action='editContact.php' method='POST'";
	echo "	 name='editContact' onsubmit='return validateEditContact()'>";
	echo "		<h3>Edit Contact</h3>";
	echo "		<input name='contactID' type='hidden' value='{$contactID}' />";

	echo "		<label>First name</label>";
	echo "		<input name='firstName' value='{$contact['firstName']}' />";

	echo "		<label>Last name</label>";
	echo "		<input name='lastName' value='{$contact['lastName']}' />";

	echo "		<label>Phone number</label>";
	echo "		<input name='did' type='number' value='{$contact['did']}' />";

	echo "		<label>Notes</label>";
	echo "		<textarea name='notes'>" . $contact['notes'] . "</textarea>";

	echo "		<input type='submit' value='Save' />";
	echo "	</form>";
	echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<title>voipSMS</title>

	<script>
	function validateDID(did) {
		if(/\d{10}/.test(did))
			return (true)

		return (false)
	}

	// Make sure form is filled completely and such.
	function validateEditContact() {
	var errors = [];
	var form = document.forms['editContact'];
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
		errors.push("First Name cannot be empty.");
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
	
	// Making sure DID is valid
	if(!validateDID(form['did'].value)) {
		errors.push("Contact phone number isn't valid.");
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

	// Checking if we're logged in
	if(!isset($_SESSION['auth'])) {
		echo "<div id='error'>Error: You must be logged in to visit this page</div>"; } else {
		// If HTTP GET, display the form. 
		// If HTTP POST, process the form, and redirect to contact list
		if($_SERVER['REQUEST_METHOD'] == "GET") {
			/**************************************************
				HTTP GET

				Display the form, if we're given a contactID 
			*/
			// Make sure that the user actually selected a contact
			//	either in the contactList form, or from returning to this form
			if(!isset($_REQUEST['contactID'])) {
				echo "<div id='error'>Error: No contact ID given.</div>";
			} else {
				displayEditContactForm($_REQUEST['contactID']);
			}
		} else {
			/**************************************************
				HTTP POST

				Process the form. If not valid input, show the form again
			*/
			if(isset($_POST['contactID'])
				&& isset($_POST['firstName'])
				&& isset($_POST['lastName'])
				&& isset($_POST['did'])) {
				
				// $_POST['notes'] isn't a required form field. 
				// This is just to make sure we don't bork
				if(!isset($_POST['notes']))
					$notes = "";
				else
					$notes = $_POST['notes'];

				// Update the contact
				// (This makes sure that we actually own the contact before altering).
				$ret = updateContact($_SESSION['auth_info']['userID'],
					$_POST['contactID'],
					$_POST['firstName'],
					$_POST['lastName'],
					$_POST['did'],
					$notes);

				if($ret['status'] == "success") {
					echo "<div class='message'>Contact updated.</div>";
				} else {
					echo "<div class='message'>Contact not updated (reason: {$ret['status']}";
					echo "</div>";
				}

				echo "<a href='contactList.php'>Back to contacts list</a>";
				//header("Location: contactList.php");

			} else {
				echo "<div id='error'>Error: Missing some form data!</div>"; 
			}
		}
	}
	?>
</body>
</html>
