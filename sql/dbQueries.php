<?php
require_once('dbinfo.php');
/**************************************************
	Local DB actions

	These will be common queries done on the DB.
*/

/**************************************************
	delete User DID

	Removes all of a user's dids
*/
function deleteUserDIDS($ownerID) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();
		
		// Getting all of the contacts for this user
		$select_stmt = $db->prepare("DELETE FROM `dids` WHERE ownerID = :ownerID");
		$select_stmt->bindValue(":ownerID", $ownerID);
		$select_stmt->execute();
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}

/**************************************************
	Get user DIDs

	Returns an array of user DIDs
*/
function getDIDs($userID) {
	$db = connectToDB();

	// Getting all DIDs for this user
	try {
		$db = connectToDB();
		
		// Getting all of the contacts for this user
		$select_stmt = $db->prepare("SELECT * FROM `dids` WHERE ownerID = :userID");
		$select_stmt->bindValue(":userID", $userID);
		$select_stmt->execute();
		
		// Grab all the data
		$res = $select_stmt->fetchAll(PDO::FETCH_ASSOC); 
		//print_r($res);
		return $res;
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}

/**************************************************
	Get user API Credentials

	Returns the user's email and base64 encoded API password
*/
function getUserAPICredentials($userID) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();
		
		// Getting all of the contacts for this user
		$query = "SELECT vms_email, vms_apiPassword 
			FROM `users` WHERE userID = :userID";

		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":userID", $userID);
		$select_stmt->execute();
		
		// Grab all the data
		$res = $select_stmt->fetchAll(PDO::FETCH_ASSOC); 
		//print_r($res);
		return $res;
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}

/**************************************************
	Get user from email/password

	Returns the user's ID, email and base64 encoded API password
*/
function getUserFromLogin($vms_email, $vms_password) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();                                                 

		// Validating user login against db                                  
		$stmt = $db->prepare("SELECT userID, name
		FROM users WHERE                                                 
		vms_email=:vms_email AND userPassword=SHA2(:userPassword,256)"); 
		
		$stmt->bindValue(":vms_email", trim($vms_email));           
		$stmt->bindValue(":userPassword", trim($vms_password));     
		$stmt->execute();                                                    
		
		// Checking if we've got a match                                     
		if($stmt->rowCount() == 1) {                                         
			$userData = $stmt->fetchAll(PDO::FETCH_ASSOC);                   
			return $userData;
		} else {
			return False;
		}
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
		return False;
	}
}
?>
