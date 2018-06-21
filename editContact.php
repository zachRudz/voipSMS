<?php 
session_start();
require_once("sql/dbQueries.php");

function displayEditContactForm($contactID) {
	// Getting the contact
	$contact = getContact($_SESSION['auth_info']['userID'], $contactID);

	// Making sure things are k (Found the contact, and it belongs to $userID)
	if($contact['status'] == "not_found") {
		echo "<div class='alert alert-danger'><strong>Error:</strong>
			No contact found with that ID.</div>";
		return;
	} else if($contact['status'] == "not_owner") {
		echo "<div class='alert alert-danger'><strong>Error:</strong>
			This contact does not belong to you!</div>";
		return;
	} else if($contact['status'] != "success") {
		echo "<div class='alert alert-danger'><strong>Error:</strong>
			Unable to get contact.</div>";
		return;
	}

    echo "
    <h1 class='h3 font-weight-normal'>Add a contact</h1>

	<form action='editContact.php' method='POST'
		name='editContact' onsubmit='return validateEditContact()'>
		
	<input name='contactID' type='hidden' value='{$contactID}' />

    <div class='form-group'>
        <label class='col-sm-2' for='firstNameInput'>First name</label>
        <div class='col-sm-10'>
            <input class='form-control' id='firstNameInput' name='firstName'
                placeholder='First Name' value='{$contact['firstName']}' required />
        </div>
    </div>

    <div class='form-group'>
        <label class='col-sm-2' for='lastNameInput'>Last name</label>
        <div class='col-sm-10'>
            <input class='form-control' id='lastNameInput' name='lastName' 
                placeholder='Last Name' value='{$contact['lastName']}' required />
        </div>
    </div>

    <div class='form-group'>
        <label class='col-sm-2' for='didInput'>Phone Number</label>
        <div class='col-sm-10'>
            <input type='number' class='form-control' id='didInput' 
                placeholder='1112223333' name='did' required value='{$contact['did']}' />
        </div>
    </div>


    <div class='form-group'>
        <label class='col-sm-2' for='notesInput'>Notes</label>
        <div class='col-sm-10'>
            <textarea class='form-control' rows='5' id='notesInput' name='notes'>{$contact['notes']}</textarea>
        </div>
    </div>

	<input type='submit' value='Save' />
    </form>
    ";
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
	echo "<div id='formErrorMessage'></div>";

	// Checking if we're logged in
	if(!isset($_SESSION['auth'])) {
		echo "<div id='alert alert-danger'><strong>Error:</strong>
			You must be logged in to visit this page</div>"; 
	} else {

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
				echo "<div id='alert alert-danger'><strong>Error:</strong>
					No contact ID given.</div>"; 
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

				// Let the user know what happened.
				if($ret['status'] == "success") {
					echo "<div class='alert alert-success'><strong>Success!</strong> 
						Contact updated. 
						<a class='alert-link' href='contactList.php'>Back to contacts list</a>
						</div>";
				} else {
					echo "<div class='alert alert-danger'><strong>Error:</strong> 
						Contact not updated (reason: {$ret['status']}).
						<a class='alert-link' href='contactList.php'>Back to contacts list</a>
						</div>";
				}
				//header("Location: contactList.php");

			} else {
				echo "<div class='alert alert-danger'><strong>Error!</strong> 
					Missing some form data!
					<a class='alert-link' href='contactList.php'>Back to contacts list</a>
					</div>"; 
			}
		}
	}
	?>
</body>
<?php require_once("pageBottom.php"); ?>

<script>
function validateDID(did) {
	var re = new RegExp("^\\d+$");
	return re.test(did);
}

// Make sure form is filled completely and such.
function validateEditContact() {
	var errors = [];
	var form = document.forms['editContact'];
	var errorMessage = document.getElementById('formErrorMessage');
	
	// Clear error classes from inputs
	errorMessage.classList.remove('alert');
	errorMessage.classList.remove('alert-danger');
	
	// Clear the error div
	errorMessage.innerHTML = "";
	
	// -- Begin processing form --
	// Making sure values aren't empty
	if(form['firstName'].value == "") {
		errors.push("First Name cannot be empty.");
	}
	
	if(form['lastName'].value == "") {
		errors.push("Last name cannot be empty.");
	}
	
	if(form['did'].value == "") {
		errors.push("Contact phone number cannot be empty.");
	}
	
	// Making sure DID is valid
	console.log(form['did'].value);
	console.log(validateDID(form['did'].value));
	if(!validateDID(form['did'].value)) {
		errors.push("Contact phone number isn't valid.");
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
		
		errorMessage.classList.add('alert');
		errorMessage.classList.add('alert-danger');
		return false;
	}
	
	return true;
}
</script>
</html>
