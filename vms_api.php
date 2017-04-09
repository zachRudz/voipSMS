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
	SMS Fetch and Send
*/
function getSMS($api_username, $base64_api_password, $from, $to, $contact, $limit) {
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

?>
