<?php
	/************************************************** 
		NOTICE:

		Replace the below variables with your own values.
	*/
	$dbName = "";
	$dbUser = "";
	$dbPass = "";

	// Connects to the database, and returns a PDO object
	function connectToDB() {
		$db = new PDO("mysql:host=localhost;dbname={$dbName}", 
			$dbUser, $dbPass);
		
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $db;
	}
?>
