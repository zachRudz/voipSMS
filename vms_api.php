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
	require_once('sql/dbinfo.php');
	$db = connectToDB();

	// Getting the user from the DB
	$select_stmt = $db->prepare("SELECT userID, vms_email, vms_apiPassword
		FROM `users` WHERE userID = :userID");
	$select_stmt->bindValue(":userID", $userID);
	$select_stmt->execute();

	// If we've found the user, make the rest call using the found parameters
	if($select_stmt->rowCount() == 1) {
		$userData = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

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
	} else {
		// No user with that ID found; Return an array of status="userID_not_found""
		return array("status"=>"userID_not_found");
	}
}

// Only gets messages sent to/from a user's $did, as opposed to all of them
function getSMS($userID, $from, $to, $did, $contact, $limit) {
	require_once('sql/dbinfo.php');
	$db = connectToDB();

	// Getting the user from the DB
	$select_stmt = $db->prepare("SELECT userID, vms_email, vms_apiPassword
		FROM `users` WHERE userID = :userID");
	$select_stmt->bindValue(":userID", $userID);
	$select_stmt->execute();

	// If we've found the user, make the rest call using the found parameters
	if($select_stmt->rowCount() == 1) {
		$userData = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

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
	} else {
		// No user with that ID found; Return an array of status="userID_not_found""
		return array("status"=>"userID_not_found");
	}
}
?>
