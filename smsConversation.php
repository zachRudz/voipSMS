<?php
require_once("vms_api.php");
require_once("sql/dbQueries.php");
/**************************************************
	-- SMS Conversation --
	This file contains functions related to a single conversation
	IE: The messages from one of your phone numbers to a contact number.
	
**************************************************/

/**************************************************
	-- displaySMSConversationSearchForm() --
	Similar to the displayConversationSearchForm(), this
	function will allow the user to search for the SMS messages.

	We're reffering to the contact as $target so that
	we can differentiate the form results as being a search
	for one or more conversation, or the SMS messages in a 
	single conversation.

	Could we just have a separate hidden field that would tell the difference?
	Absolutely.
	Are we going to do that?
	Absolutely not.
	Why?
	Don't wanna.
*/
function displaySMSConversationSearchForm($target) {
	echo '<div id="conversationFilter">';
	echo '  <h3>Search for a Conversation</h3>';
	echo '  <form action="sms.php" method="get">';
	echo '      <label>From </label>';
	echo '      <input type="date" name="from" />';
	
	echo '      <label>To</label>';
	echo '      <input type="date" name="to" />';
	
	echo '      <label>Contact</label>';
	echo '      <input type="input" name="target" value="' .$target.'" />';
	
	echo '      <label>Limit of texts to search</label>';
	echo '      <input type="number" name="limit" min="0" value="25" />';
	
	echo '      <input type="submit" name="submit" value="filter" />';
	echo '  </form>';
	echo '</div>';
}

/**************************************************
	-- Get Conversation --
	Gets the past SMS messages from one user DID to a contact, given the
	relevant parameters.

	Parameters:
		$to: Date search threshold
		$from: Date search threshold
		$contact: User DID
		$limit: Number of messages to search for
*/
function getConversation($userID, $to, $from, $did, $contact, $limit) {
	// -- Validate parameters --
	$ret = array("errors"=> array(), "status"=> "success");

	// Making sure we're logged in
	if(!isset($userID)) {
		$ret['status'] = "failure";
		$ret['errors'][] = "No user ID given";
	}

	// Making sure parameters are set
	if(!isset($did)) {
		$ret['status'] = "failure";
		$ret['errors'][] = "No user DID given.";
	}
	if(!isset($contact)) {
		$ret['status'] = "failure";
		$ret['errors'][] = "No contact DID given.";
	}

	// Quitting now if there's any errors
	if($ret['status'] == "failure") {
		return $ret;
	}


	// Setting defaults if things aren't supplied
	if($to = "") {
		$to = "today";
	}
	if($from = "") {
		$from = "today";
	}
	if($limit= "") {
		$limit = "25";
	}

	// Making the SMS call
	$smsHistory = getSMS(
		$userID,
		$to,
		$from,
		$did,
		$contact,
		$limit);
	return $smsHistory;	
}

/**************************************************
	-- Display Conversation History --

	Formats the results of getConversation into some
	spicey neato HTML
*/
function displayConversationHistory($conversation) {
	echo "<div id='conversation'>";

	// Making sure that there's actually some SMS messages to parse
	if($conversation['status'] == "no_sms") {
		echo "No messages found; Did you search for a broad enough time window?";
		echo "</div>";
		return;
	} else if($conversation['status'] != "success") {
		echo "Something went wrong (Reason: {$conversation['status']})";
		echo "</div>";
		return;
	}

	// Looping through each SMS
	foreach($conversation['sms'] as $sms) {
		// Different CSS for recieved/sent messages
		if($sms['type'] == 1) {
			echo "<div class='message recieved'>";
		} else {
			echo "<div class='message sent'>";
		}

		echo "<div class='messagePayload'>";
		echo htmlspecialchars($sms['message']);
		echo "</div>";

		echo "<div class='messageDate'>";
		echo $sms['date'];
		echo "</div>";

		echo "</div>";

	}

	echo "</div>";
}
?>
