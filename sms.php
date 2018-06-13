<?php
session_start();
require_once('vms_api.php');
require_once('conversationHistory.php');
require_once("smsConversation.php");

require_once("pageTop.php");
require_once("imports/datatables_css.php");
?>
	<title>voipSMS: SMS Center</title>

</head>
<body onload="scrollToBottom()">
<?php 
	include_once("header.php");
	// Make sure we're logged in
	if(!isset($_SESSION['auth'])) {
		echo '<div class="alert alert-danger">
			Error: You must be logged in first.</div>';

	// User is logged in
	} else {

		// Attempt to save the user's DID to the DB.
		// This will fail if the DID recieved via the form ($_REQUEST) doesn't belong to the user.
		// In that case, don't bother setting the $_SESSION variable.
		if(isset($_REQUEST['activeDID'])) {
			// Attempt to update the default DID in the DB
			if(setDefaultDID($_SESSION['auth_info']['userID'], $_REQUEST['activeDID'])) {
				$_SESSION['auth_info']['activeDID'] = htmlspecialchars($_REQUEST['activeDID']);

				echo "<div class='alert alert-success'><strong>Success:</strong>
					Active DID updated.</div>";
			} else {
				echo "<div class='alert alert-danger'><strong>Error:</strong>
					Unable to set the default DID.</div>";
			}
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
				$limit = "25";

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
							echo "<div class='alert alert-danger'>";
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

		// User has not selected an SMS target.
		// Display the conversation search form on HTTP GET, or display the results on HTTP POST
		} else {

			/***************************************************************************
				Search for a conversation

				On HTTP GET, display a form to let the user filter for a conversation.
				On HTTP POST, display the search results of the filter.
					Also display the form to let them to filter again.
				
			***************************************************************************/
			echo "<div class='container-fluid rounded border border-primary'>";
			displayConversationSearchForm();
			if($_SERVER['REQUEST_METHOD'] == "POST") {

				// If the user wants to search for a history of conversations
				if($_POST['submit'] = "search") {
					// Validate that the post data is all there
					if(isset($_POST['from'])
						&& isset($_POST['to'])
						&& isset($_POST['contact'])
						&& isset($_POST['limit'])) {
						$smsSearchResults = searchForConversation($_POST['from'],
							$_POST['to'],
							$_POST['did'],
							$_POST['contact'],
							$_POST['limit']);
						
						// Make sure we didn't bork
						if($smsSearchResults['status'] != "success") {
							// Conversation search failed
							// Let the user know why
							echo '<div class="alert alert-danger">
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
						echo '<div class="alert alert-danger">
							Error: There is missing information in the conversation search form.
						</div>';
					}
				} 
			} // Submitted conversation search form?
			echo "</div>";
		} // SMS conversation or search for conversation?
	} 
?>
</body>
<?php require_once("pageBottom.php"); ?>
<?php require_once("imports/datatables.php"); ?>

<script>
	// JQuery DataTable stuff for the contact pane
	$(document).ready(function(){
		$('#contactPaneContacts').DataTable({
			"pageLength": 25
		});
	});

	// JQuery DataTable stuff for the conversation history search
	$(document).ready(function(){
		$('#conversations').DataTable({
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
</html>
