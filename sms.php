<?php
session_start();
require_once('sql/dbinfo.php');
require_once('vms_api.php');
require_once('conversationHistory.php');
require_once("smsConversation.php");
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<title>voipSMS: SMS Center</title>

	<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
	<script src="//cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
	<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" />

	<script>
		// JQuery DataTable stuff for the contact pane
		$(document).ready(function(){
			$('#contactPaneContacts').DataTable({
				"pageLength": 25
			});
		});

		/**************************************************
		 Spicey scriptaroonie to jump to the bottom of the webpage when the page loads

		 This is because the newest texts should be at the bottom of the page; 
		 Which the user prolly wants to see.
		*/
		function scrollToBottom() {
			window.scrollTo(0,document.body.scrollHeight);
		}
	</script>

	<script src="js/smsValidation.js"></script>
</head>
<body onload="scrollToBottom()">
<?php 
	include_once("header.php");
	// Make sure we're logged in
	if(!isset($_SESSION['auth'])) {
		echo '<div class="error">
		Error: You must be logged in first.
		</div>';
	} else {
		// Test if the user got here via changing their active DID.
		// If so, set the $_SESSION variable accordingly and continue doing 
		//	whatever they were doing.
		// $_REQUEST['target'] should be set if they were in a conversation.
		if(isset($_REQUEST['activeDID'])) {
			$_SESSION['auth_info']['activeDID'] = htmlspecialchars($_REQUEST['activeDID']);
		}

		// Print the conversation pane
		// This will be a nifty lil side panel with links to text your contacts,
		// switch your active DID, and text new numbers 
		if(isset($_REQUEST['target'])) {
			displayContactPane($_SESSION['auth_info']['userID'], $_REQUEST['target']);
		} else {
			displayContactPane($_SESSION['auth_info']['userID'], "");
		}

		// Determine what to show the user
		// If the user has clicked a link to text a contact DID
		if(isset($_REQUEST['target'])) {

			// Making sure that $target isn't an empty string.
			// If it were, getSMS() would fetch contacts from EVERYONE
			if(trim($_REQUEST['target']) != "") {
				/***************************************************************************
					SMS Conversation

					A conversation is an SMS chat between a user DID and an
					arbitrary target DID.

					Actions:
						- Display a form to filter for SMS messages by this user
						- Retrieve the SMS history from this user
						- Form to send SMS message

				***************************************************************************/
				// Getting filter results, if any
				$to = "";
				$from = "";
				$limit = "100";

				// Filter dates
				if(isset($_REQUEST['to']))
					$to = $_REQUEST['to'];	

				if(isset($_REQUEST['from']))
					$from = $_REQUEST['from'];	

				// Filter limit
				if(isset($_REQUEST['limit']))
					$limit = $_REQUEST['limit'];	

				// Testing if we've sent an SMS.
				//	Do the bad thing if so
				if($_SERVER['REQUEST_METHOD'] == "POST") {
					if(isset($_POST['message'])) {
						// Pushing the payload
						$sendSMSResult = sendSMS($_SESSION['auth_info']['userID'],
							$_POST['target'],
							$_POST['message']);

						if($sendSMSResult['status'] != "success") {
							// SMS Sending failed 
							// Let the user know why
							echo "<div class='error'>";
							echo "Error: Sending of SMS message failed (Status: {$sendSMSResult['status']}) ";
							// If something went wrong on the vms side, this won't be set.
							// However, if local server-side validation failed, we will have 
							//	errors to go through.
							if(isset($sendSMSResult['errors'])) {
								echo 'Reasons: <ul>';
								foreach($sendSMSResult['errors'] as $errors) {
									echo "<li>{$errors}</li>";
								}
								echo '</ul>';
							}

							echo "</div>";
						}
					}
				}

				// Display the conversation filter
				displaySMSConversationSearchForm($_REQUEST['target']);

				$convo = getConversation($_SESSION['auth_info']['userID'],
					$from, $to,
					$_SESSION['auth_info']['activeDID'],
					$_REQUEST['target'],
					$limit);


				//echo "to: [{$to}] <br />";
				//echo "from: [{$from}]<br />";
				//echo "did: [{$_SESSION['auth_info']['activeDID']}]<br />";
				//echo "contact: [{$_REQUEST['target']}]<br />";
				//echo "limit: [{$limit}]<br />";
				//echo "<br />";
				//print_r($_SESSION['auth_info']);
				//echo "<br />";
				//print_r($convo);

				// Print the conversation
				displayConversationHistory($convo);

				// Print the form to send SMS
				displaySendSMSForm($_REQUEST['target']);

			} // Make sure $target != ""
		} else {

			/***************************************************************************
				Search for a conversation

				On HTTP GET, display a form to let the user filter for a conversation.
				On HTTP POST, display the search results of the filter.
					Also display the form to let them to filter again.
				
			***************************************************************************/
			displayConversationSearchForm();
			if($_SERVER['REQUEST_METHOD'] == "POST") {

				// If the user wants to search for a history of conversations
				if($_POST['submit'] = "search") {
					// Creating artificial dates if they weren't filled out by the user
					// Default range: [($current_time - 1 month), $current_time]
					$from = $_POST['from'];
					$to = $_POST['to'];

					if(trim($_POST['from']) == "") {
						$from = Date("Y-m-d", strtotime("-1 months"));
					}
					if(trim($_POST['to']) == "") {
						$to = Date('Y-m-d');
					}

					// Validate that the post data is all there
					if(isset($from)
						&& isset($to)
						&& isset($_POST['contact'])
						&& isset($_POST['limit'])) {
						$smsSearchResults = searchForConversation($from,
							$to,
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
						} else {
							// Conversation search succeeded, display results
							displayConversations($smsSearchResults);
						}
					} else { 
						// Conversation form wasn't filled out properly. Complain moar
						echo '<div class="error">
						Error: There is missing information in the conversation search form.
						</div>';
					}
				} 
			} // Submitted conversation search form?
		} // SMS conversation or search for conversation?
	} 
?>
</body>
</html>
