<?php
session_start();
require_once('sql/dbQueries.php');

/**************************************************
	Print add contact form
*/
function printAddContactForm() {
	echo '
	<h1 class="h3 font-weight-normal">Add a contact</h1>

	<form action="addContact.php" method="POST"
		name="addContact" onsubmit="return validateAddContact()">

	<div class="form-group">
		<label class="col-sm-2" for="firstNameInput">First name</label>
		<div class="col-sm-10">
			<input class="form-control" id="firstNameInput" 
				placeholder="First Name" name="firstName" required />
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2" for="lastNameInput">Last name</label>
		<div class="col-sm-10">
			<input class="form-control" id="lastNameInput" 
				placeholder="First Name" name="lastName" />
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2" for="didInput">Phone Number</label>
		<div class="col-sm-10">
			<input type="number" class="form-control" id="didInput" 
				placeholder="1112223333" name="did" required />
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2" for="notesInput">Notes</label>
		<div class="col-sm-10">
			<textarea class="form-control" rows="5" id="notesInput" name="notes"></textarea>
		</div>
	</div>

	<input type="submit">
	</form>
	';
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

	echo "<div class='alert alert-success'><strong>Success!</strong> Added contact successfully.";
	echo "<a href='contactList.php'>Back to contact list</a></div>";
	printAddContactForm();                                         
}



/**************************************************
    Entry Point
*/
require_once("pageTop.php");
?>
	<title>voipSMS</title>
	
</head>
<body>
<?php 
	include_once("header.php");
	echo "<div class='container py-2 my-2 rounded'>";
	echo "<div id='formErrorMessage'></div>";

	// Make sure the user is actually logged in
	if(!isset($_SESSION['auth'])) {
		echo "<div id='alert alert-error'><strong>Error:</strong>
			You must be signed in to visit this page!</div>";
	} else {
		// If the form was submitted, attempt to create a contact
		// Otherwise, print the contact form
		if($_SERVER['REQUEST_METHOD'] === "POST") {
			createContact();
		} else {
			printAddContactForm();
		}
	}
	echo "</div>";
?>
</body>
<?php require_once("pageBottom.php"); ?>

<script>
function validateDID(did) {
    var re = new RegExp("^\\d+$");
    return re.test(did);
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
	errorMessage.classList.remove('alert');
	errorMessage.classList.remove('alert-danger');
	
	// Clear the error div
	errorMessage.innerHTML = "";
	
	// -- Begin processing form --
	// Making sure values aren't empty
	if(form['firstName'].value == "") {
		errors.push("First name cannot be empty.");
		form['firstName'].classList.add('formError');
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
		errorMessage.innerHTML = "<strong>Error:</strong> The form wasn't filled out properly:";
		
		for(var i = 0; i < numErrors; i++) {                               
			errorMessage.innerHTML += "<br />";                            
			errorMessage.innerHTML += errors[i];                           
		}                                                                  
		
		errorMessage.classList.add('alert');                               
		errorMessage.classList.add('alert-danger');                               
		return false;                                                      
	}                                                                      
	
	return true;                                                           
}
</script>
</html>
