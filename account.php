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
		echo "<div id='error'>Error: No user found with that ID.</div>";
		return;
	}
	
	// Begin printing the form
	echo "<div class='formWrapper'>";
	echo "	<h3>Edit Account Information</h3>";
	echo "	<p>";
	echo "	Empty fields will be left unchanged. <br />";
	echo "	If you want to change your voipSMS password, 
		make sure you enter your current voipSMS password.";
	echo "</p>";

	echo "  <form action='account.php' method='POST'>";
	echo "      <input name='userID' type='hidden' value='{$userID}' />";
	
	echo "      <label>Name</label>";
	echo "      <input name='name' value='{$user['name']}' />";
	
	echo '		<label>Password </label>';                        
	echo '		<input type="password" name="password" />';       

	echo '		<label>Confirm password </label>';                
	echo '		<input type="password" name="password2" />';      

	echo '		<label>Current password </label>';                
	echo '		<input type="password" name="currentPassword" />';      
	
	echo '		<label>voip.ms API Password </label>';            
	echo '		<input type="password" name="vms_apiPassword" />';

	echo "      <input type='submit' name='submit' value='Save' />";
	echo "  </form>";
	echo "</div>";


	// Begin printing syncDIDs form
	echo "<div class='formWrapper'>";
	echo "  <form action='account.php' method='POST'>";
	echo "		<h3>Sync DIDs with VoIP.MS</h3>";
	echo "		<input type='submit' name='submit' value='Sync DIDs' />";
	echo "	</form";
	echo "</div>";
}

/**************************************************
	Process Account Form

	Processes the form from displayAccountForm()
*/
function processAccountChanges() {
	// Setting form fields to "" if they're not filled out

	if(isset($_POST['name'])
		&& isset($_POST['userID'])
		&& isset($_POST['name'])
		&& isset($_POST['password'])
		&& isset($_POST['password2']) 
		&& isset($_POST['currentPassword']) 
		&& isset($_POST['vms_apiPassword'])) {

		// -- Making sure that if values are not entered, that they'll be k --
		// userID
		if(trim($_POST['userID']) == "")
			$userID= "";
		else
			$userID= $_POST['userID'];

		// name
		if(trim($_POST['name']) == "")
			$name = "";
		else
			$name = $_POST['name'];

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
				echo "<div id='error'>Error: Passwords don't match!</div>";
			}
		}

		// Make the dank changes
		return alterUser($userID,
			$name,
			$vms_apiPassword,
			$userPassword,
			$currentPassword);
	} else {
		echo "<div id='error'>Error: Form not filled out properly. </div>";
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

	// Make sure we're logged in
	if(!isset($_SESSION['auth'])) {
		echo "<div id='error'>Error: You must be logged in to visit this page</div>";
	} else {
		// Display the form on HTTP GET
		//	or process the form on HTTP POST if it was filled out
		//	or sync the dids on HTTP POST if the user clicked that button
		if($_SERVER['REQUEST_METHOD'] == "POST") {
			if($_POST['submit'] == 'Save') {
				// Processing the account form
				if(processAccountChanges()) {
					echo "<div class='message'>";
					echo "Changes applied successfully.";
					echo "</div>";
				}

				// Test if the user's change to the api password is valid
				$user = getUser($_SESSION['auth_info']['userID']);
				$res = validateLogin($user['vms_email'], 
					base64_decode($user['vms_apiPassword']));

				if($res['status'] != "success") {
					echo "<div class='warning'>";
					echo "Warning: The current voip.ms API password doesn't validate";
					echo "(Reason: {$res['status']})</div>";
				}

				// Updating the session variable
				$_SESSION['auth_info']['name'] = $user['name'];
			} else if($_POST['submit'] == 'Sync DIDs') {

				// Syncing DIDs.	
				if(syncUserDIDs($_SESSION['auth_info']['userID'])) {
					echo "<div class='message'>DIDs synced successfully</div>";

					// Updating the active did
					$dids = getDIDs($_SESSION['auth_info']['userID']);
					$_SESSION['auth_info']['activeDID'] = $dids[0]['did'];
				} else {
					echo "<div class='message'>DIDs not synced.</div>";
				}
			} else {
				// wat
				echo "<div id='error'>Unexpected form submission recieved.</div>";
			}
		}

		// Display the account form
		displayAccountForm($_SESSION['auth_info']['userID']);
	}
?>
</body>
</html>
