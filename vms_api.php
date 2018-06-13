<?php
/**************************************************
	voip.ms REST wrappers
*/
function makeRESTCall($parameters) {
	// Prepare URL for rest call
	$parameterStr = "";
	foreach ($parameters as $key=>$value) {
		$parameterStr = $parameterStr . $key . "=" . rawurlencode($value) . "&";
	}

	// Cut off the last trailing ampersand
	$parameterStr = substr($parameterStr, 0, -1);


	// Begin curl request
	// This snippit was taken+modified from the voip.ms rest api examples
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_URL, "https://voip.ms/api/v1/rest.php?" . $parameterStr);
	$result = curl_exec($ch);
	curl_close($ch);
	
	return json_decode($result,true);
} 

/**************************************************
	Debug
*/
// getLanguage()
// Returns an assoc array of languages or something
// Pretty much for debugging & verifying if we succeeded the rest call
function getLanguages($api_username, $base64_api_password) {
	// Obfuscating the api password in the db
	$api_password= base64_decode($base64_api_password);

	$parameters = array("api_username"=>$api_username,
		"api_password"=>$api_password,
		"method"=>"getLanguages");

	return makeRestCall($parameters);
}

function validateLogin($api_username, $api_password) {
	$parameters = array("api_username"=>$api_username,
		"api_password"=>$api_password,
		"method"=>"getLanguages");

	return makeRestCall($parameters);
}

/**************************************************
	SMS Fetch 

	getSMS_manualAuth(): 
		- Manually specify email and base64 encoded api password
		- Debug pretty much
	
	getSMS_allDIDs():
		- Fetches user's email and api password from the local DB
		- Fetches vms SMS sent to/from ALL of the user's DIDs
	
	getSMS():
		- Fetches user's email and api password from the local DB
		- Fetches vms SMS sent to/from ONE of the user's DIDs
*/
function getSMS_manualAuth($api_username, $base64_api_password, $from, $to, $contact, $limit) {
	// Obfuscating the api password in the db
	$api_password = base64_decode($base64_api_password);

	$parameters = array("api_username"=>$api_username,
		"api_password"=>$api_password,
		"method"=>"getSMS",
		"from"=>$from,
		"to"=>$to,
		"contact"=>$contact,
		"limit"=>$limit);

	return makeRestCall($parameters);
}

// Uses email/parameters from DB
function getSMS_allDIDs($userID, $from, $to, $contact, $limit) {
	// Getting the user from the DB
	$userData = getUserAPICredentials($userID);
	if(count($userData) != 1) {
		// No user with that ID found; Return an array of status="userID_not_found""
		return array("status"=>"userID_not_found");
	}

	// Un-Obfuscating the api password in the db
	$api_password = base64_decode($userData[0]["vms_apiPassword"]);

	$parameters = array("api_username"=>$userData[0]["vms_email"],
		"api_password"=>$api_password,
		"method"=>"getSMS",
		"from"=>$from,
		"to"=>$to,
		"contact"=>$contact,
		"limit"=>$limit);

	return makeRestCall($parameters);
}

// Only gets messages sent to/from a user's $did, as opposed to all of them
function getSMS($userID, $from, $to, $did, $contact, $limit) {
	// Getting the user from the DB
	$userData = getUserAPICredentials($userID);
	if(count($userData) != 1) {
		// No user with that ID found; Return an array of status="userID_not_found""
		return array("status"=>"userID_not_found");
	}

	// If we've found the user, make the rest call using the found parameters
	// Un-Obfuscating the api password in the db
	$api_password = base64_decode($userData[0]["vms_apiPassword"]);

	$parameters = array("api_username"=>$userData[0]["vms_email"],
		"api_password"=>$api_password,
		"method"=>"getSMS",
		"from"=>$from,
		"to"=>$to,
		"did"=>$did,
		"contact"=>$contact,
		"limit"=>$limit);

	return makeRestCall($parameters);
}

// Only gets messages sent to/from a user's $did, as opposed to all of them
function vms_sendSMS($userID, $activeDID, $target, $message) {
	// Getting the user from the DB
	$userData = getUserAPICredentials($userID);
	if(count($userData) != 1) {
		// No user with that ID found; Return an array of status="userID_not_found""
		return array("status"=>"userID_not_found");
	}

	// If we've found the user, make the rest call using the found parameters
	// Un-Obfuscating the api password in the db
	$api_password = base64_decode($userData[0]["vms_apiPassword"]);

	$parameters = array("api_username"=>$userData[0]["vms_email"],
		"api_password"=>$api_password,
		"method"=>"sendSMS",
		"did"=>$activeDID,
		"dst"=>$target,
		"message"=>$message);

	return makeRestCall($parameters);
}
/**************************************************
	DIDs

	Returns the information about all of the user's DIDs
*/
function getUserDIDs($userID) {
	// Getting the user from the DB
	$userData = getUserAPICredentials($userID);
	if(count($userData) != 1) {
		// No user with that ID found; Return an array of status="userID_not_found""
		return array("status"=>"userID_not_found");
	}

	// If we've found the user, make the rest call using the found parameters
	// Un-Obfuscating the api password in the db
	$api_password = base64_decode($userData[0]["vms_apiPassword"]);

	$parameters = array("api_username"=>$userData[0]["vms_email"],
		"api_password"=>$api_password,
		"method"=>"getDIDsInfo");

	return makeRestCall($parameters);
}

/**************************************************
	Sync user DIDs
	
	This script will remove all the user's dids from the db. and add all the dids
	from the voip.ms servers.
*/
function syncUserDIDs($userID) {
	// Makes sure that the user ID is set
	if(!isset($userID)) {
		return False;
	}
	
	// Getting new DIDS
	// Doing this first so that if something borks, then we don't wipe the user's DIDS
	//  when we can't replace them
	$dids = getUserDIDs($userID);
	if($dids['status'] != "success") {
		echo "<div class='error'>Error: Couldn't fetch the user's DIDs from voip.ms server. 
			(Reason: {$dids['status']}) </div>";
		return False;
	}

	// Clears the user's default DID
	clearDefaultDID($userID);
	
	// Clears all of the DIDS for this user
	deleteUserDIDS($userID);
	
	// Adding the dids we just got from the vms server
	// Making a standalone DB call here since it's more efficient to reuse an insert statement
	//  than setting up PDO every time
	try {
		$db = connectToDB();
		
		$query = "INSERT INTO `dids` (ownerID, did) VALUES (:ownerID, :did)";
		$insert_stmt = $db->prepare($query);
		$insert_stmt->bindValue("ownerID", $userID);
		
		// Looping through all the DIDs we just found
		foreach($dids['dids'] as $d) {
			//echo "Added user DID [{$d['did']}]<br />";
			$insert_stmt->bindValue("did", $d['did']);
			$insert_stmt->execute();
		}

		return True;
	} catch(Exception $e) {
		echo "<div class='error'>Exception: " . $e->getMessage() ."</div>";
		return False;
	}
}


?>
