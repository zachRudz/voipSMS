<?php 
session_start();
require_once('sql/dbQueries.php');
require_once('vms_api.php');

/**************************************************
	Display Account Form

	Displays the options to change the user's account information

	They can opt to...
		- Change their name
		- Change their vms api password
		- Change their user password
	
	And also via another form (read: submit button)...
		- Sync their DIDs with voip.ms servers
	

	Depending on what value $_POST['submit'] has, we will process the form,
		or sync the user's DIDs
*/
function displayAccountForm($userID) {
	// Getting the user
	$user = getUser($userID);

	// Making sure we got the user
	if($user == False) {
		echo "<div class='alert alert-danger'><strong>Error:</strong> No user found with that ID.</div>";
		return;
	}

	// A place for all the errors 
	echo "<div id='formErrorMessage'></div>";
	
	// Begin printing the update user info form
	echo "
	<div class='container py-2 my-2 rounded'>
	<h1 class='h3 font-weight-normal'>Edit Account Information</h3>
	<p>Empty fields will be left unchanged. </p>

	<form action='account.php' method='POST'
		name='accountChange' onsubmit='return validateAccountChange()'>
		
		<h4>Change password</h4>
		<div class='form-group row'>
			<label class='col-sm-2 col-form-label' for='currentPasswordInput'>Current Password</label>
			<div class='col-sm-10'>
				<input type='password' class='form-control' id='currentPasswordInput' 
					placeholder='Current Password' name='currentPassword' />
			</div>

			<label class='col-sm-2 col-form-label' for='passwordInput'>New Password</label>
			<div class='col-sm-10'>
				<input type='password' class='form-control' id='passwordInput' 
					placeholder='New Password' name='password' />
			</div>

			<label class='col-sm-2 col-form-label' for='password2Input'>Confirm Password</label>
			<div class='col-sm-10'>
				<input type='password' class='form-control' id='password2Input' 
					placeholder='New Password' name='password2' />
			</div>
		</div>
		
		<h4>Change VoIP.ms API Password</h4>
		<div class='form-group row'>
			<label class='col-sm-2 col-form-label' for='vms_apiPassword'>VoIP.MS API Password</label>
			<div class='col-sm-10'>
				<input type='password' class='form-control' id='vms_apiPassword' 
					placeholder='VoIP.MS API Password' name='vms_apiPassword' />
			</div>
		</div>

		<input type='submit' name='submit' value='Save' />
	</form>
	</div>";

	// Begin printing syncDIDs form
	echo "
	<div class='container py-2 my-2 rounded'>
	<h1 class='h3 font-weight-normal'>Sync DIDs with VoIP.MS</h3>
	<form action='account.php' method='POST' name='syncDids'>
		<input type='submit' name='submit' value='Sync DIDs' />
	</form>
	</div>";

	// Begin printing delete account
	echo "
	<div class='container py-2 my-2 rounded'>
	<h1 class='h3 font-weight-normal'>Delete account</h3>
	<div>To delete your account, enter your current password.</div>
	<div class='alert alert-warning'><strong>Warning!</strong> 
		This action cannot be undone!</div>

	<form action='account.php' method='POST'
		name='accountDelete' onsubmit='return validateAccountDelete()'>

		<div class='form-group'>
			<div class='row'>
				<label class='col-sm-2 col-form-label' for='passwordInput'>Password</label>
				<div class='col-sm-10'>
					<input type='password' class='form-control' id='passwordInput' 
						placeholder='Confirm Current Password' name='password' />
				</div>
			</div>
			<div class='form-check'>
				<input type='checkbox' class='form-check-input' id='accountDeleteCheck' 
					value='confirm' name='accountDeleteCheck' /> 
				<label class='form-check-label' for='accountDeleteCheck'>
					I confirm that I want to delete my account
				</label>
			</div>
		</div>

		<input type='submit' name='submit' value='Delete Account' />
	</form>
	</div>";
}

/**************************************************
	Process Account Form

	Processes the form from displayAccountForm()
*/
function processAccountChanges() {
	// Setting form fields to "" if they're not filled out

	if(isset($_POST['password'])
		&& isset($_POST['password2']) 
		&& isset($_POST['currentPassword']) 
		&& isset($_POST['vms_apiPassword'])) {

		// -- Making sure that if values are not entered, that they'll be k --
		// userID
		$userID = $_SESSION['auth_info']['userID'];


		// password 
		if(trim($_POST['password']) == "")
			$userPassword = "";
		else
			$userPassword = $_POST['password'];

		// currentPassword
		if(trim($_POST['currentPassword']) == "")
			$currentPassword = "";
		else
			$currentPassword = $_POST['currentPassword'];

		// vms_apiPassword
		if(trim($_POST['vms_apiPassword']) == "")
			$vms_apiPassword = "";
		else
			$vms_apiPassword = $_POST['vms_apiPassword'];

		// Make sure that if passwords are set, then they're the same
		if(trim($_POST['password']) == "") {
			if(trim($_POST['password']) != trim($_POST['password'])) {
				echo "<div class='alert alert-danger'><strong>Error:</strong> Passwords don't match!</div>";
				return;
			}
		}

		// Make sure that the password meets the length requirement
		if(strlen($_POST['password']) < 8 && $_POST['password'] != "") {
				echo "<div class='alert alert-danger'><strong>Error:</strong>
					New password doesn't meet length requirements!</div>";
				return;
		}

		// Make the dank changes
		return alterUser($userID,
			$vms_apiPassword,
			$userPassword,
			$currentPassword);
	} else {
		echo "<div class='alert alert-danger'><strong>Error:</strong>
			Form not filled out properly. </div>";
		return;
	}
}

/**************************************************
	Process Account Deletion

	Processes the form from displayAccountForm()
*/
function processAccountDeletion() {
	// Make sure that the user confirmed that they want to delete their account
	if(isset($_POST['password']) 
		&& isset($_POST['accountDeleteCheck'])) {

		// User filled out the form. Make sure that they've entered the correct password.
		$user = getUser($_SESSION['auth_info']['userID']);
		if(getUserFromLogin($user['vms_email'], $_POST['password']) != false) {
			
			// User has confirmed that they want to delete their account.
			return deleteUser($_SESSION['auth_info']['userID']);
			
		// User didn't enter their password correctly
		} else {
			echo "<div class='alert alert-danger'><strong>Error: </strong>
				Password not correct. Not deleting account.</div>";
			return false;
		}

	// User didn't complete the form (ie: password and checkbox)
	} else {
		echo "<div class='alert alert-danger'><strong>Error: </strong>
			Unable to delete account, because not all of the form was filled out.</div>";
		return false;
	}
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

	// Make sure we're logged in
	if(!isset($_SESSION['auth'])) {
		echo "<div id='error'>Error: You must be logged in to visit this page</div>";
	} else {
		// Display the form on HTTP GET
		//	or process the form on HTTP POST if it was filled out
		//	or sync the dids on HTTP POST if the user clicked that button
		if($_SERVER['REQUEST_METHOD'] == "POST") {
			// User is changing their passwords. 
			if($_POST['submit'] == 'Save') {

				// Attempt to update the user's password, and API password.
				// If it succeeded, then we should consider syncing their DIDs.
				if(processAccountChanges()) {

					// User's account was updated successfully.
					// Test if the user's change to the api password is valid
					// Don't bother trying if the user didn't attempt to change their API password
					if(isset($_POST['vms_apiPassword'])) {
						$user = getUser($_SESSION['auth_info']['userID']);
						$res = validateLogin($user['vms_email'], 
							base64_decode($user['vms_apiPassword']));
		
						if($res['status'] != "success") {
							echo "<div class='alert alert-warning'>
							<strong>Warning!</strong>
							 VoIP.ms API password saved successfully, 
							 but it doesn't validate against their server!
							 (Reason: {$res['status']})</div>";
						}
					}
				}


			// Syncing DIDs.	
			} else if($_POST['submit'] == 'Sync DIDs') {
				if(syncUserDIDs($_SESSION['auth_info']['userID'])) {
					echo "<div class='alert alert-success'><strong>Success!</strong>
						DIDs have been synced.</div>";

					// Updating the active did
					$dids = getDIDs($_SESSION['auth_info']['userID']);
					$_SESSION['auth_info']['activeDID'] = $dids[0]['did'];
				} else {
					echo "<div class='alert alert-danger'><strong>Error: </strong>
						Unable to sync DIDs</div>";
				}


			// Deleting account
			} else if($_POST['submit'] == 'Delete Account') {
				// Process the account deletion. 
				// If it worked, display the success message, and be done with it. 
				// Otherwise, show the error message, and go back to the account form.
				if(processAccountDeletion()) {
				
					// Delete the user's session
					session_destroy();
					
					// Let the user know it worked, and send them home.
					echo "<div class='alert alert-success'><strong>Success! </strong>
						Your account has been deleted.
						<a href='index.php' class='alert-link'>Back home</a></div>";
					return;

				// Something's gone wrong with the account deletion.
				} else {
					echo "<div class='alert alert-danger'><strong>Error: </strong>
						Something went wrong when trying to delete your account.</div>";
				}


			// Unexpected form recieved. User isn't editing account info, syncing DIDs, or deleting their acc.
			// They're probably misusing with the forms.
			} else {
				echo "<div class='alert alert-danger'><strong>Error:</strong>
					Unexpected form submission recieved.</div>";
			}
		}

		// Display the account form
		displayAccountForm($_SESSION['auth_info']['userID']);
	}
?>
</body>
<?php require_once("pageBottom.php"); ?>

<script>
// Make sure form is filled completely and such.
function validateAccountChange() {
	var errors = [];
	var form = document.forms['accountChange'];
	var errorMessage = document.getElementById('formErrorMessage');
	
	// Clear error classes from inputs
	errorMessage.classList.remove('alert');
	errorMessage.classList.remove('alert-danger');
	
	// Clear the error div
	errorMessage.innerHTML = "";
	
	// -- Begin processing form --
	// User filled out passwords, but the new passwords don't match
	if(form['password'].value != form['password2'].value) {
		errors.push("You're attempting to change your password, but they don't match.");
	}

	// Password is too short
	if(form['password'].value.length < 7 && form['password'].value != "") {
		errors.push("Your new password is too short (Min 8 characters)");
	}

	// Making sure that the user fills out their current password if they wanna change it.
	if(form['currentPassword'].value != "") {
		// User filled out current password, but not the new ones
		if(form['password'].value == "") {
			errors.push("You're attempting to change your password," +
				" but you didn't fill out the new password.");
		}

		if(form['password2'].value == "") {
			errors.push("You're attempting to change your password," +
				" but you didn't fill out the confirmation password.");
		}

	} else {
		// User didn't fill out their current password, but they want to change it
		if(form['password'].value != "") {
			errors.push("Enter your current password too, if you want to change it.");
		}
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

// Make sure the user is prepared to delete their account
function validateAccountDelete() {
	var errors = [];
	var form = document.forms['accountDelete'];
	var errorMessage = document.getElementById('formErrorMessage');
	
	// Clear error classes from inputs
	errorMessage.classList.remove('alert');
	errorMessage.classList.remove('alert-danger');
	
	// Clear the error div
	errorMessage.innerHTML = "";
	
	// -- Begin processing form --
	// Password is too short
	if(form['password'].value == "") {
		errors.push("No password entered!");
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
