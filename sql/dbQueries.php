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

/**************************************************
	Get Contact

	Returns the contact that was searched for, if it's owned by the correct user.

	$res['status'] possible values:
		"success": Contact found successfully. $res contains contact information.
		"not_owner": Contact not owned by $userID. 
		"not_found": Contact not found in DB at all.
*/
function getContact($userID, $contactID) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();                                                 

		// Validating user login against db                                  
		// Getting all of the contacts for this user                                    
		$query = "SELECT * FROM contacts WHERE contactID = :contactID";
		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":contactID", $contactID);     
		$select_stmt->execute();

		// Checking if we've got a match                                     
		if($select_stmt->rowCount() == 1) {                                         
			$userData = $select_stmt->fetchAll(PDO::FETCH_ASSOC);                   

			if($userData[0]['ownerID'] != $userID)
				return array("status"=>"not_owner");

			$userData[0]['status'] = "success";
			return $userData[0];
		} else {
			return array("status"=>"not_found");
		}
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
		return False;
	}
}

/**************************************************
	Update Contact

	Modifys an existing contact in the DB.

	Checks to make sure that the contact actually is owned by the person first.
*/
function updateContact($userID, $contactID, $firstName, $lastName, $did, $notes) {
	// Getting the contact first; To make sure they exist, and to make sure our user owns the contact
	$contact = getContact($userID, $contactID);

	// Return the contact so that the error can be parsed on failure
	if($contact['status'] != "success")
		return $contact;
	
			
	// Begin altering the table entry
	try {
		$db = connectToDB();                                                 

		// Begin updating the contact
		$query = "UPDATE contacts SET firstName = :firstName,
			lastName = :lastName,
			did = :did,
			notes = :notes
			WHERE contactID = :contactID";

		$stmt = $db->prepare($query);
		$stmt->bindValue(":firstName", $firstName);     
		$stmt->bindValue(":lastName", $lastName);     
		$stmt->bindValue(":did", $did);     
		$stmt->bindValue(":notes", $notes);     
		$stmt->bindValue(":contactID", $contactID);     
		$stmt->execute();

		return array("status"=>"success");
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
		return False;
	}
}

/**************************************************
	Delete Contact

	Delete the contact, regardless of the owner (care).
*/
function deleteContact($contactID) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();                                                 

		// Validating user login against db                                  
		// Getting all of the contacts for this user                                    
		$query = "DELETE FROM contacts WHERE contactID = :contactID";
		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":contactID", $contactID);     
		$select_stmt->execute();
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught while deleting a contact: " . $e->getMessage() . "</div>";
		return False;
	}
}

/**************************************************
	Add Contact

	Add the contact to the DB, regardless of errors.
*/
function addContact($ownerID, $firstName, $lastName, $did, $notes) {
	try {
		$db = connectToDB();                                                 

		$query = "INSERT INTO `contacts` 
			(`ownerID`, `firstName`, `lastName`, `did`, `notes`) 
			VALUES (:ownerID, :firstName, :lastName, :did, :notes)";

		$add_stmt = $db->prepare($query);
		$add_stmt->bindValue(":ownerID", trim($_SESSION['auth_info']['userID']));
		$add_stmt->bindValue(":firstName", trim($_POST['firstName']));
		$add_stmt->bindValue(":lastName", trim($_POST['lastName']));
		$add_stmt->bindValue(":did", trim($_POST['did']));
		$add_stmt->bindValue(":notes", trim($_POST['notes']));
		$add_stmt->execute();
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught while adding contact to DB: ";
		echo $e->getMessage() . "</div>";
		return False;
	}
}
?>
