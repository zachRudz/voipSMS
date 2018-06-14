<?php
require_once('dbinfo.php');

/**************************************************
	Local DB actions

	These will be common queries done on the DB.

	-- User DIDs --
	deleteUserDIDS()
	getDIDFromValue()
	getDIDFromID()
	getDIDs()
	clearDefaultDID()
	setDefaultDID()

	-- Users --
	createUser()
	getUserAPICredentials()
	getUser()
	getUserFromLogin()
	alterUser()
	deleteUser()
	isEmailUnique()

	-- Contacts --
	getContact()
	getContacts()
	updateContact()
	deleteContact()
	addContact()

	-- Admin --
	isAdmin()
	deleteUsers()
*/

/**************************************************
	delete User DID

	Removes all of a user's dids
*/
function deleteUserDIDS($ownerID) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();

		// Nulling out the reference to the owner's default DID
		$query = "UPDATE users SET didID_default = NULL where userID = :ownerID";
		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":ownerID", $ownerID);
		$select_stmt->execute();
		
		// Getting all of the contacts for this user
		$query = "DELETE FROM `dids` WHERE ownerID = :ownerID";
		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":ownerID", $ownerID);
		$select_stmt->execute();
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}

/**************************************************
	Get DID From Value

	Returns the DID with phone number = $did.
	Returns false if the DID doesn't exist.
*/
function getDIDFromValue($didValue) {
	$db = connectToDB();

	// Getting all DIDs for this user
	try {
		$db = connectToDB();
		
		// Getting all of the contacts for this user
		$select_stmt = $db->prepare("SELECT * FROM `dids` WHERE did = :did");
		$select_stmt->bindValue(":did", $didValue);
		$select_stmt->execute();
		
		// Grab all the data
		$res = $select_stmt->fetchAll(PDO::FETCH_ASSOC); 

		// Validate
		if(count($res) != 1)
			return False;

		return $res[0];
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}

/**************************************************
	Get DID From ID

	Returns the DID with ID = $didID.
	Returns false if the DID doesn't exist.
*/
function getDIDFromID($didID) {
	$db = connectToDB();

	// Getting all DIDs for this user
	try {
		$db = connectToDB();
		
		// Getting all of the contacts for this user
		$select_stmt = $db->prepare("SELECT * FROM `dids` WHERE didID = :didID");
		$select_stmt->bindValue(":didID", $didID);
		$select_stmt->execute();
		
		// Grab all the data
		$res = $select_stmt->fetchAll(PDO::FETCH_ASSOC); 

		// Validate
		if(count($res) != 1)
			return False;

		return $res[0];
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
	Clear Default DID

	Removes the default DID for a user (ie: Sets it to null).
	Returns True if it was successful, and false otherwise.
*/
function clearDefaultDID($userID) {
	// Making sure the user exists
	$user = getUser($userID);
	if($user == False) {
		echo "<div class='error'>Error: That user doesn't exist.</div>";
		return False;
	} 

	// Begin altering the table entry
	try {
		$db = connectToDB();                                                 

		// Updating default DID for the user
		$query = "UPDATE users SET didID_default = null
			WHERE userID = :userID";
	
		$stmt = $db->prepare($query);
		$stmt->bindValue(":userID", $userID);     

		$stmt->execute();
		return True;
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
		return False;
	}
}

/**************************************************
	Update Default DID

	Updates the default DID for a user.
	Returns true if successful, and false if the DID doesn't belong to the user.
*/
function setDefaultDID($userID, $didValue) {
	// Making sure the user exists
	$user = getUser($userID);
	if($user == False) {
		echo "<div class='error'>Error: That user doesn't exist.</div>";
		return False;
	} 

	// Making sure the DID exists
	$did = getDIDFromValue($didValue);
	if($did == False && $did != null) {
		echo "<div class='error'>Error: That DID doesn't exist (didID: " . $didValue. ").</div>";
		return False;
	} 

	// Making sure the user owns the DID
	if($did['ownerID'] != $user['userID']) {
		echo "<div class='error'>Error: That user doesn't own that DID.</div>";
		return False;
	}

	// Begin altering the table entry
	try {
		$db = connectToDB();                                                 

		// Updating default DID for the user
		$query = "UPDATE users SET didID_default = :didID
			WHERE userID = :userID";
	
		$stmt = $db->prepare($query);
		$stmt->bindValue(":didID", $did['didID']);     
		$stmt->bindValue(":userID", $userID);     

		$stmt->execute();
		return True;
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
		return False;
	}
}

function createUser($vms_email, $vms_apiPassword, $userPassword) {
	try {
		$db = connectToDB();                                                 

	    // -- Adding user to db --
	    $query = "INSERT INTO `users` 
			(`vms_email`, `vms_apiPassword`, `userPassword`) 
			VALUES (:vms_email, :vms_apiPassword, SHA2(:userPassword,256))";
	
		$add_stmt = $db->prepare($query);
		$add_stmt->bindValue(":vms_email", trim($vms_email));
		$add_stmt->bindValue(":vms_apiPassword", base64_encode(trim($vms_apiPassword)));
		$add_stmt->bindValue(":userPassword", trim($userPassword));
		$add_stmt->execute();
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught while adding contact to DB: ";
		echo $e->getMessage() . "</div>";
		return False;
	}

	return True;
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
	Get user 

	Returns the all of the user's info
*/
function getUser($userID) {
	// Getting all info for this user
	try {
		$db = connectToDB();
		
		// Getting the user's information given their ID
		$query = "SELECT * FROM `users` LEFT JOIN dids ON didID_default = didID" . 
			" WHERE userID = :userID";

		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":userID", $userID);
		$select_stmt->execute();
		
		// Grab all the data
		$res = $select_stmt->fetchAll(PDO::FETCH_ASSOC); 

		// Validate
		if(count($res) != 1)
			return False;

		return $res[0];
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}

/**************************************************
	Get user info from login

	Returns the user's ID, email, and default DID if the user exists.
	Return false otherwise.
*/
function getUserFromLogin($vms_email, $userPassword) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();
		
		// Getting all of the contacts for this user
		$query = "SELECT userID, vms_email, didID_default
			FROM `users` WHERE 
			vms_email=:vms_email AND userPassword=SHA2(:userPassword, 256)";

		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":vms_email", $vms_email);
		$select_stmt->bindValue(":userPassword", $userPassword);
		$select_stmt->execute();
		
		// Grab all the data
		if($select_stmt->rowCount() == 1) {
			$userData = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
			return $userData;
		} else {
			return False;
		}
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}

/**************************************************
	Alter user

	Changes the values of an existing user.
	If a value is null, don't change it.
*/
function alterUser($userID, $vms_apiPassword, $userPassword, $currentPassword) {
	// Making sure the user exists
	$user = getUser($userID);
	if($user == False) {
		echo "<div class='error'>Cannot change user password (current password incorrect)</div>";
		return False;
	}


	// Begin altering the table entry
	try {
		$db = connectToDB();                                                 

		// Updating the API password if it needs updating
		if(trim($vms_apiPassword) != "") {
			$new_vms_apiPassword = base64_encode(trim($vms_apiPassword));

			// User is changing their password
			$query = "UPDATE users SET vms_apiPassword = :vms_apiPassword
				WHERE userID = :userID";
	
			$stmt = $db->prepare($query);
			$stmt->bindValue(":vms_apiPassword", trim($new_vms_apiPassword));     
			$stmt->bindValue(":userID", $userID);     
			$stmt->execute();
			echo "<div class='alert alert-success'><strong>Success!</strong>
				API password updated.</div>";
		}


		// Updating the usser's password 
		if(trim($userPassword != "")) {
			// Making sure that the current password matches
			if($user['userPassword'] != hash("sha256", trim($currentPassword))) {
				// Current password doesn't validate
				echo "<div class='alert alert-danger'><strong>Error:</strong>
					Cannot change user password (current password incorrect)</div>";

				return False;

			} else {
				// Changing the user's password
				$query = "UPDATE users SET userPassword = SHA2(:userPassword,256)
					WHERE userID = :userID";
	
				$stmt = $db->prepare($query);
				$stmt->bindValue(":userPassword", trim($userPassword));     
				$stmt->bindValue(":userID", $userID);     
				$stmt->execute();
				echo "<div class='alert alert-success'><strong>Success!</strong>
					User password updated.</div>";
			}
		}

		// All is well
		return True;
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught: " . $e->getMessage() . "</div>";
		return False;
	}
}

/**************************************************
	Delete user (single)

	Given a user ID, delete them

	This means deleting all of their contacts, DIDs, and then the user itsself.
*/
function deleteUser($userID) {
	try {
		$db = connectToDB();                                                 

		// Deleting all of the user's contacts
		$deleteContactQuery = "DELETE FROM contacts WHERE ownerID = :userID";
		$deleteContact_stmt = $db->prepare($deleteContactQuery );
		$deleteContact_stmt->bindValue(":userID", $userID);     
		$deleteContact_stmt->execute();

		// Clear the default DID of the user
		clearDefaultDID($userID);

		// Deleting all of the user's dids
		$deleteDidsQuery = "DELETE FROM dids WHERE ownerID = :userID";
		$deleteDids_stmt = $db->prepare($deleteDidsQuery );
		$deleteDids_stmt ->bindValue(":userID", $userID);     
		$deleteDids_stmt ->execute();

		// Deleting the user
		$deleteUserQuery = "DELETE FROM users WHERE userID = :userID";
		$deleteUser_stmt = $db->prepare($deleteUserQuery );
		$deleteUser_stmt ->bindValue(":userID", $userID);     
		$deleteUser_stmt ->execute();

		return true;
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught while deleting a contact: ";
		echo $e->getMessage() . "</div>";
		return False;
	}
}

/**************************************************
	Is Email Unique

	Returns True if no user exists with the supplied email
*/
function isEmailUnique($vms_email) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();                                                 

		// Validating user login against db                                  
		$stmt = $db->prepare("SELECT userID
		FROM users WHERE
		vms_email=:vms_email"); 
		
		$stmt->bindValue(":vms_email", trim($vms_email));           
		$stmt->execute();                                                    
		
		// Checking if we've got a match                                     
		return $stmt->rowCount() == 0;
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
	Get Contacts

	Returns all of the contacts for the user

	Returns false if something went wrong.
*/
function getContacts($userID) {
	// Getting all DIDs for this user
	try {
		$db = connectToDB();                                                 

		// Validating user login against db                                  
		// Getting all of the contacts for this user                                    
		$query = "SELECT * FROM contacts WHERE ownerID = :ownerID";
		$select_stmt = $db->prepare($query);
		$select_stmt->bindValue(":ownerID", $userID);     
		$select_stmt->execute();

		// Checking if we've got a match                                     
		$userData = $select_stmt->fetchAll(PDO::FETCH_ASSOC);                   
		return $userData;
	} catch(Exception $e) {
		echo "<div class='alert alert-danger'>Exception caught: " . $e->getMessage() . "</div>";
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

/**************************************************
	Is Admin

	Returns True/False depending on whether or not the user is
	an admin or not.
*/
function isAdmin($userID) {
	$user = getUser($userID);

	if($user['userType'] == 'A')
		return True;
	
	return False;
}

/**************************************************
	Delete users (multiple)

	Given an array of users to delete, loop through and delete them all.

	This means deleting all of their contacts, DIDs, and then the user itsself.
*/
function deleteUsers($userID_array) {
	try {
		$db = connectToDB();                                                 

		// Query for deleting contacts for a user
		$deleteContactQuery = "DELETE FROM contacts WHERE ownerID = :uid";
		$deleteContact_stmt = $db->prepare($deleteContactQuery );

		// Query for deleting DIDs for a user
		$deleteDidsQuery = "DELETE FROM dids WHERE ownerID = :uid";
		$deleteDids_stmt = $db->prepare($deleteDidsQuery );

		// Query for deleting user
		$deleteUserQuery = "DELETE FROM users WHERE userID = :uid";
		$deleteUser_stmt = $db->prepare($deleteUserQuery );


		// Looping through each user
		foreach($userID_array as $uid) {
			// Deleting all of the user's contacts
			$deleteContact_stmt->bindValue(":uid", $uid);     
			$deleteContact_stmt->execute();

			// Clear the default DID of the user
			clearDefaultDID($uid);

			// Deleting all of the user's dids
			$deleteDids_stmt ->bindValue(":uid", $uid);     
			$deleteDids_stmt ->execute();

			// Deleting the user
			$deleteUser_stmt ->bindValue(":uid", $uid);     
			$deleteUser_stmt ->execute();
		}

		return true;
	} catch(Exception $e) {
		echo "<div class='error'>Exception caught while deleting a contact: ";
		echo $e->getMessage() . "</div>";
		return False;
	}
}
?>
