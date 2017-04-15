<?php 
session_start();
require_once("sql/dbQueries.php");

function displayEditContactForm($contactID) {
	// Getting the contact
	$contact = getContact($_SESSION['auth_info']['userID'], $contactID);

	// Making sure things are k (Found the contact, and it belongs to $userID)
	if($contact['status'] == "not_found") {
		echo "<div id='error'>Error: No contact found with that ID.</div>";
		return;
	} else if($contact['status'] == "not_owner") {
		echo "<div id='error'>Error: This contact does not belong to you!</div>";
		return;
	} else if($contact['status'] != "success") {
		echo "<div id='error'>Unexpected error: Unable to get contact.</div>";
		return;
	}

	echo "<div id='contactEditForm'>";
	echo "	<form action='editContact.php' method='POST'>";
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
</head>
<body>
	<?php 
	include_once("header.php"); 

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
