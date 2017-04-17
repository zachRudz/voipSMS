<?php
require_once("vms_api.php");
require_once("sql/dbinfo.php");
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
	echo '<div class="formWrapper">';
	echo '  <h3>Filter this conversation conversation</h3>';
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
function getConversation($userID, $from, $to, $did, $contact, $limit) {
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
	if(trim($to) == "") {
		$to = Date('Y-m-d');
	}
	if(trim($from) == "") {
		$from = Date('Y-m-d');
	}
	if(trim($limit) == "") {
		$limit = "25";
	}

	// Making the SMS call
	$smsHistory = getSMS( $userID,
		$from,
		$to,
		$did,
		$contact,
		$limit);

	//echo "to: [{$to}] <br />";                                
	//echo "from: [{$from}]<br />";                             
	//echo "did: [{$did}]<br />";
	//echo "contact: [{$contact}]<br />";            
	//echo "limit: [{$limit}]<br />";                           
	//echo "<br />";                                            
	//print_r($smsHistory);                                          

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

	// Messages are returned sorted by newest first; we want newer texts at the bottom.
	// Looping through each SMS
	//foreach($conversation['sms'] as $sms) {
	$index = count($conversation['sms']);
	while($index) {
		$index--;
		$sms = $conversation['sms'][$index];

		// Different CSS for recieved/sent messages
		if($sms['type'] == 1) {
			echo "<div class='sms recieved'>";
		} else {
			echo "<div class='sms sent'>";
		}

		echo "<div class='smsPayload'>";
		echo htmlspecialchars($sms['message']);
		echo "</div>";

		echo "<div class='smsDate'>";
		echo $sms['date'];
		echo "</div>";

		echo "</div>";
	}

	echo "</div>";
}

/**************************************************
	Display active DID change form

	The user has an active DID that they send SMS messages from.
	This form will allow the user to change it. 

	*****

	Form returns back to sms.php, back to the conversation between
	$newUserDID and $currentContact.
	
	If no $currentContact is supplied, the user will be returned to 
	the conversation search form.
*/
function displayActiveDIDChangeForm($userID, $currentContact) {
	// -- Printing the DID selection dropdown --
	// This is a dropdown form that will allow the user to switch their active DID
	$dids = getDIDs($userID);
	echo '<div id="didSelection">
	<form action="sms.php" method="get">
		<select name="activeDID">';
	
	// Printing each did
	foreach($dids as $d) {
		echo "<option value='{$d['did']}'";

		if($d['did'] == $_SESSION['auth_info']['activeDID'])
			echo " selected";

		echo ">{$d['did']}</option>";
	}
	echo '</select>';
	
	// Return to the conversation we were in, if there was one
	if($currentContact != "") {
		echo '<input type="hidden" name="target" value="' . $currentContact . '" />';
	}

	echo '<input type="submit" value="Change active DID"/>
	</form>
	</div>';
}

function displayContactPaneContacts($userID) {
	try{
		$db = connectToDB();

		// Making the query
		$query = "SELECT * FROM contacts WHERE ownerID = :userID";
		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":userID", $userID);
		$select_stmt->execute();

		// Creating a table, to be formatted by a jquery datatable
		echo "<table id='contactPaneContacts' class='display'>
			<thead>
				<tr>
					<th>First name</th>
					<th>Last name</th>
					<th>Number</th>
				</tr>
			</thead>
			<tbody>";

		// Printing table contents
		while($data_array = $select_stmt->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr>";
				echo "<td><a href='sms.php?target={$data_array['did']}'>";
					echo "{$data_array['firstName']}";
				echo "</a></td>";
				echo "<td><a href='sms.php?target={$data_array['did']}'>";
					echo "{$data_array['lastName']}";
				echo "</a></td>";
				echo "<td><a href='sms.php?target={$data_array['did']}'>";
					echo "{$data_array['did']}";
				echo "</a></td>";
			echo "</tr>";
		}
		echo "</tbody>
		</table>";
		
	} catch(Exception $e) {
		echo "<div id='error'>Exception caught while displaying contact pane: ";
		echo $e->getMessage() . "</div>";
	}
}

/**************************************************
	Get a list of contacts, and throw them all in a neat lil
	bar to the side of the page.

	This pane will contain a form to switch the current user DID (Session var),
	and links to sms.php?target=$contact.
*/
function displayContactPane($userID, $currentContact) {
	// Begin building the pane
	echo "<div id='contactPane'>";
	echo "<h3>DID Options</h3>";

	// Printing active DID selection form
	displayActiveDIDChangeForm($userID, $currentContact);

	// Print form to let the user text a new DID
	// This would be something that they would input via an HTML form
	echo '<form action="sms.php" method="get">';
		echo '<input type="text" name="target">';
		echo '<input type="submit" value="Text new number"/>';
	echo '</form>';

	// Get all of the user's contacts
	echo "<h3>Contacts</h3>";
	displayContactPaneContacts($userID); 

	// Finish building the pane
	echo "</div>";
}
?>
