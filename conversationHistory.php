<?php
require_once('sql/dbinfo.php');
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
	echo '<div id="conversationFilter">';
	echo '	<h3>Search for a Conversation</h3>';
	echo '	<form action="sms.php" method="post">';
	echo '		<label>From </label>';
	echo '		<input type="date" name="from" />';

	echo '		<label>To</label>';
	echo '		<input type="date" name="to" />';

	echo '		<label>Target Contact</label>';
	echo '		<input type="number" name="contact" />';

	echo '		<label>Limit of texts to search</label>';
	echo '		<input type="number" name="limit" min="0" />';

	echo '		<input type="submit" name="submit" value="search" />';
	echo '	</form>';
	echo '</div>';
}

/**************************************************
	Search Conversation

	Fetch SMS messages from the voip.ms server with the supplied filter parameters.

	Separate the results into a neat little array, based on which user DID the
	message was sent to/from. Return this array:
		$return['status'] => "True"/"False"
		$return['errors'] => []		
	 	$return['messages'][$userDID] =>
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
function searchForConversation($to, $from, $contact, $limit) {
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
		$return['status'] = False;
		$errors[] = "From date is not valid.";
	}
	if(!preg_match($regexDateMatch, $to)) {
		$return['status'] = False;
		$errors[] = "To date is not valid.";
	}
	
	// Make sure the date range is valid
	$fromDate = DateTime::createFromFormat("Y-m-d", $from);
	$toDate = DateTime::createFromFormat("Y-m-d", $to);
	if($fromDate > $toDate) {
		$return['status'] = False;
		echo "from: {$from}";
		echo "<br />to: {$to}<br />";
		$errors[] = "To date cannot come before From date.";
	}

	// Make sure the limit is a positive value
	if($limit < 1) {
		$return['status'] = False;
		$errors[] = "Limit is not valid";
	}


	// Testing if we've failed validation
	if($return['status'] == False) {
		$return['errors'] = $errors;

		echo "Return:<br />";
		print_r($return);
		return $return;
	}


	// -- Get the SMS messages --
	$smsSearch = getSMS_db($_SESSION["auth_info"]["userID"],
		$from, $to, $contact, $limit);


	echo "Return:<br />";
	print_r($return);
	echo "<br />smsSearch:<br />";
	print_r($smsSearch);
	return;
	// If the SMS search failed, return immediately
	if($smsSearch['status'] != "success") {
		return $smsSearch;
	}

	echo "Return:<br />";
	print_r($return);
	echo "<br />smsSearch:<br />";
	print_r($smsSearch);
	return;

	// -- Parsing the messages --
	// We want a nice clean array as described above, so we can cleanly iterate 
	//		through it later
	//foreach($smsSearch['sms'] as $sms) {
	//		
	//}
	//$return['messages'] = array();
	//

	//// -- Return --
	//return $conversations;
}
?>
