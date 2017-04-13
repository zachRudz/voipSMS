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
	<script>
		/**************************************************
		 Spicey scriptaroonie to jump to the bottom of the webpage when the page loads

		 This is because the newest texts should be at the bottom of the page; 
		 Which the user prolly wants to see.
		*/
		function scrollToBottom() {
			window.scrollTo(0,document.body.scrollHeight);
		}
	</script>
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
		// Determine what to show the user
		// If the user has clicked a link to text a contact DID
		if(isset($_REQUEST['target'])) {
			require_once("smsConversation.php");
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
			$limit = "25";
			$_SESSION['auth_info']['activeDID'] = "5199903661";

			// Filter dates
			if(isset($_REQUEST['to']))
				$to = $_REQUEST['to'];	

			if(isset($_REQUEST['from']))
				$from = $_REQUEST['from'];	

			// Filter limit
			if(isset($_REQUEST['limit']))
				$limit = $_REQUEST['limit'];	

			$convo = getConversation($_SESSION['auth_info']['userID'],
				$to, $from,
				$_SESSION['auth_info']['activeDID'],
				$_REQUEST['target'],
				$limit);

			// Display the conversation filter
			displaySMSConversationSearchForm($_REQUEST['target']);

			echo "to: [{$to}] <br />";
			echo "from: [{$from}]<br />";
			echo "did: [{$_SESSION['auth_info']['activeDID']}]<br />";
			echo "contact: [{$_REQUEST['target']}]<br />";
			echo "limit: [{$limit}]<br />";
			echo "<br />";
			print_r($_SESSION['auth_info']);
			echo "<br />";
			print_r($convo);

			// Print the conversation
			displayConversationHistory($convo);




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
			} // Submitted conversation search form?
		} // SMS conversation or search for conversation?
	} 
?>
</body>
</html>
