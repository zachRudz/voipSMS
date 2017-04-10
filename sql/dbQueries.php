<?php
require_once('dbinfo.php');
/**************************************************
	Local DB actions

	These will be common queries done on the DB.
*/

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
		echo "<div id='error'>Exception caught: " . $e->getMessage() . "</div>";
	}
}
?>
