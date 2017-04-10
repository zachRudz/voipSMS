<?php
session_start();
require_once('sql/dbinfo.php');
require_once('vms_api.php');
require_once('conversationHistory.php');
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<title>voipSMS: SMS Center</title>
</head>
<body>
<?php 
	include_once("header.php");
	// Make sure we're logged in
	if(!isset($_SESSION['auth'])) {
		echo '<div class="error">
		Error: You must be logged in first.
		</div>';
	} else {
		// Determine what to show the user

		if($_SERVER['REQUEST_METHOD'] == "GET") {
			// No contact selected to text; Let them search through a history of conversations,
			//	or select a contact from the contact menu
			displayConversationSearchForm();
		} else if($_SERVER['REQUEST_METHOD'] == "POST") {

			// If the user wants to search for a history of conversations
			if($_POST['submit'] = "search") {
				// Validate that the post data is all there
				if(isset($_POST['from'])
					&& isset($_POST['to'])
					&& isset($_POST['contact'])
					&& isset($_POST['limit'])) {
					$smsSearchResults = searchForConversation($_POST['to'],
						$_POST['from'],
						$_POST['did'],
						$_POST['contact'],
						$_POST['limit']);
					
					// Make sure we didn't bork
					if($smsSearchResults['status'] != "success") {
						// Conversation search failed
						// Let the user know why
						echo '<div class="error">
							Error: Conversation search failed. Reasons:
							<ul>';
						foreach($smsSearchResults['errors'] as $errors) {
							echo "<li>{$errors}</li>";
						}
						echo '</ul></div>';
					}

					// Conversation search succeeded, display results
					displayConversations($smsSearchResults);
				} else { 
					// Conversation form wasn't filled out properly. Complain moar
					echo '<div class="error">
					Error: There is missing information in the conversation search form.
					</div>';
				}
			} 
		}
	} 
?>
</body>
</html>
