<?php
/**************************************************
	-- Conversations --

	This file contains functions related to bulk conversations
	IE: The set of conversations between a user DID and a contact number
		(User phone number 1) <-> (123 123 1234)
		(User phone number 2) <-> (123 123 1234)
		(User phone number 1) <-> (555 555 5555)
	
	With these functions, you can search for a set of conversations
	based on a time frame, user/contact phone numbers, and the number of texts
	to search for.

	-- Functions --
	displayConversationSearchForm()
	searchForConversations()
	displayConversations()

**************************************************/

require_once('sql/dbinfo.php');
require_once('sql/dbQueries.php');
require_once('vms_api.php');

/**************************************************
	Display Conversation Search Form

	Allow the user to filter through their conversation history.

	Filter for...
		- Start/end date
		- Contact DID
		- Limit (how many texts to search for)
*/
function displayConversationSearchForm() {
	// Getting the list of the user's DIDs
	$dids = getDIDs($_SESSION['auth_info']['userID']);

	echo "
		<h1 class='h3 my-3 font-weight-normal'>Search for a Conversation</h3>
		<form action='sms.php' method='post' 
			name='conversationSearch' onsubmit='return validateConversationSearch()'>

			<div class='row'>
        	    <label class='col-md-3 col-form-label' for='fromInput'>Date</label>
        	    <div class='col-md-2'>
        	        <input type='date' class='form-control' id='fromInput' 
        	            placeholder='yyyy-mm-dd' name='from' />
        	    </div>
        	    <span> to </span>
        	    <div class='col-md-2'>
        	        <input type='date' class='form-control' id='toInput' 
        	            placeholder='yyyy-mm-dd' name='to' />
        	    </div>
        	</div>
	";

	// Printing all of the user's dids
	echo "
			<div class='row'>
        	    <label class='col-md-3 col-form-label' for='didInput'>Your number</label>
        	    <div class='col-md-2'>
        	        <select class='form-control' id='didInput' name='did'>
						<option value='any'>Any</option>
	";
	foreach($dids as $d) {
		echo "		<option value='{$d['did']}'";
		
		// Select the current DID if it's the active DID
		if($d['did'] == $_SESSION['auth_info']['activeDID'])
			echo " selected";

		echo ">{$d['did']}";
		echo "</option>";
	}
	echo "
					</select>
        	    </div>
        	</div>
	";

	// Printing the target contact
	echo "
			<div class='row'>
        	    <label class='col-md-3 col-form-label' for='contactInput'>Target Contact</label>
        	    <div class='col-md-2'>
        	        <input type='number' class='form-control' id='contactInput' 
        	            placeholder='Eg: 1231231234' name='contact' />
        	    </div>
        	</div>
	";

	// Printing the limit number of messages to search for
	echo "
			<div class='row'>
        	    <label class='col-md-3 col-form-label' for='limitInput'>Limit of texts to search</label>
        	    <div class='col-md-2'>
        	        <input type='number' class='form-control' id='limitInput' 
        	            min='0' value='25' name='limit' />
        	    </div>
        	</div>
	";

	echo '		<input class="col-form-control" type="submit" name="submit" value="search" />';
	echo '	</form>';
	echo '<div id="formErrorMessage_conversationSearch"></div>';
}

/**************************************************
	Search Conversation

	Fetch SMS messages from the voip.ms server with the supplied filter parameters.

	Separate the results into a neat little array, based on which user DID the
	message was sent to/from. Return this array:
		$return['status'] => "True"/"False"
		$return['errors'] => []		
	 	$return['messages'][$userDID] =>
			[$contact] =>
				[id] => 111120
				[date] => 2014-03-30 10:24:16
				[type] => 0
				[did] => 8574884828
				[contact] => 8577884821
				[message] => hello+john
	
	where...
		id => Primary key on VMS DB
		date => Date recieved/sent
		type => Filter SMSs by Type (Boolean: 1 = received / 0 = sent)
		did => DID number for Filtering SMSs (Example: 5551234567)
		contact => Contact number for Filtering SMSs (Example: 5551234567)
		message => SMS payload
*/
function searchForConversation($from, $to, $did, $contact, $limit) {
	$return = array();
	$return['status'] = True;
	$errors = array();

	// -- Validate inputs --
	// Making sure user is logged in
	if(!isset($_SESSION['auth'])) {
		$return['status'] = False;
		$errors[] = "User is not logged in";
	}
	// Testing types
	$regexDateMatch = "/\d{4}-\d{2}-\d{2}/";

	if(!preg_match($regexDateMatch, $from)) {
		if(trim($from) == "") {
			$from = Date("Y-m-d");
		} else {
			$return['status'] = False;
			$errors[] = "From date is not valid.";
		}
	}
	if(!preg_match($regexDateMatch, $to)) {
		if(trim($to) == "") {
			$to = Date("Y-m-d");
		} else {
			$return['status'] = False;
			$errors[] = "To date is not valid.";
		}
	}

	// Make sure the limit is a positive value
	if($limit < 1) {
		if(trim($limit) == "") {
			$limit = "25";
		} else {
			$return['status'] = False;
			$errors[] = "Limit is not valid";
		}
	}


	// Testing if we've failed validation
	if($return['status'] == False) {
		$return['errors'] = $errors;
		return $return;
	}


	// -- Get the SMS messages --
	// Limit to just one DID or all dids?
	// ie; Did the user input a $did to filter for?
	if(trim($did) == "any") {
		$smsSearch = getSMS_allDIDS($_SESSION["auth_info"]["userID"],
			$from, $to, $contact, $limit);
	} else {
		$smsSearch = getSMS($_SESSION["auth_info"]["userID"],
			$from, $to, $did, $contact, $limit);
	}

	// If the SMS search failed, return immediately
	if($smsSearch['status'] != "success") {
		$smsSearch['errors'] = array($smsSearch['status']);
		return $smsSearch;
	}

	// -- Parsing the messages --
	// We want a nice clean array, so we can cleanly iterate through it later.
	// This will allow us to isolate only SMS messages to/from a single DID,
	//		as opposed to just bulking together messages from unrelated conversations
	// 
	// The format is pretty much:
	// $return['messages'][$userDID][$contactDID] => Most recent SMS

	// Loop through all of the SMS messages we just got.
	$return['messages'] = array();
	foreach($smsSearch['sms'] as $sms) {
		$did = $sms['did'];
		$contact = $sms['contact'];
		// Testing if the array index exists for this did 
		if(!isset($return['messages'][$did])) {
			$return['messages'][$did] = array();	
		}

		// Set the most recent SMS for that conversation 
		$return['messages'][$did][$contact] = $sms;
	}

	// -- Return --
	return $return;
}

/**************************************************
	Display Conversations

	Given the results of the searchForConversation(), print the SMS histories in a nice
	set of results
*/
function displayConversations($smsSearchResults) {
	// Make sure that nothing borked before doing anything
	if($smsSearchResults['status'] === "no_sms") {
		echo "<div class='message'>No messages found with those filter parameters. 
			Consider widening the filter? ({$smsSearchResults['status']})</div>";
		return;
	} else if($smsSearchResults['status'] != "success") {
		echo "<div class='error'>Error: Cannot print conversation history (Search failed)</div>";
		return;
	} 

	// Iterate through conversation histories, and print all of the conversations to a
	//	table to be fancied up by jquery's datatable
	echo '<table id="conversations">
	<thead>
		<th>Text</th>
		<th>Your phone number</th>
		<th>Their phone number</th>
		<th>Date last messaged</th>
		<th>Sent by</th>
		<th>Most recent message</th>
	</thead>
	<tbody>';

	// Looping through each user DID, target contact, and most recent SMS
	foreach($smsSearchResults['messages'] as $did) {
		foreach($did as $contact) {
			// Opting out to format date
			// I don't think datatables would sort dates as strings properly
			//$date = Date::createFromFormat("Y-m-d H:I:s", $sms["date"]);

			echo '<tr>';
			echo "<td><a href='sms.php?target=" . $contact["contact"] . "'>Text</a></td>";
			echo "<td>{$contact["did"]}</td>";
			echo "<td>{$contact["contact"]}</td>";
			echo "<td>{$contact["date"]}</td>";

			// Did the user send or recieve the SMS?
			// 0: Sent
			// 1: Recieved
			if($contact["type"] == 0) {
				echo "<td>You</td>";
			} else {
				echo "<td>Them</td>";
			}

			echo "<td>{$contact["message"]}</td>";
			echo '</tr>';
			
		}
	}
	
	echo '</tbody></table>';
}
?>
