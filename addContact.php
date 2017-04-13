<?php
session_start();

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
	require_once('sql/dbinfo.php');
	$db = connectToDB();

	// -- Validating contact info --
	$validated = True;
	$errors = array();

	// Making sure everything's set
	if(isset($_POST['firstName']) &&
	isset($_POST['lastName']) &&
	isset($_POST['did'])) {
	    // Begin testing the actual contents of the form
		
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
	$add_stmt = $db->prepare("INSERT INTO `contacts` (`ownerID`, `firstName`, `lastName`, `did`, `notes`) VALUES (:ownerID, :firstName, :lastName, :did, :notes)");

	$add_stmt->bindValue(":ownerID", trim($_SESSION['auth_info']['userID']));
	$add_stmt->bindValue(":firstName", trim($_POST['firstName']));
	$add_stmt->bindValue(":lastName", trim($_POST['lastName']));
	$add_stmt->bindValue(":did", trim($_POST['did']));
	$add_stmt->bindValue(":notes", trim($_POST['notes']));
	$add_stmt->execute();

	if($add_stmt->rowCount() != 1) {
	    $errors[] = "There was a problem saving your data to the database. Please try again later.";

		// Validation failed, let the user know.
		echo "<div>Contact validation failed:</div>";
		echo '<ul class="errors">';
		foreach($errors as $e) {
			echo "<li>" . $e . "</li>";
		}
		echo '</ul>';
	} else {
		echo "Added contact successfully.<br />";
		printAddContactForm();                                         
	}
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

	// If the form was submitted, attempt to create a contact
	// Otherwise, print the contact form
	if($_SERVER['REQUEST_METHOD'] === "POST") {
		createContact();
	} else {
		// Make sure the user is actually logged in
		if(isset($_SESSION['auth'])) {
			printAddContactForm();
		} else {
			echo "<div id='error'>Error: You must be signed in to visit this page!</div>";
		}
	}
?>
</body>
</html>
