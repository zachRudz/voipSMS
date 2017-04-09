<?php
session_start();
require_once('sql/dbinfo.php');

/**************************************************
	List Contacts

	The contact list is stored locally in the voipSMS DB.
	Print all of the contents to the table, which will later
		be sorted out by a jquery datatable.
*/
function listContacts() {
	// Printing the header and beginning of the body
	echo '<table id="contacts" class="display">
	<thead>
		<tr>
			<th>First name</th>
			<th>Last name</th>
			<th>Phone number</th>
			<th>Notes</th>
		</tr>
	</thead>
	<tbody>';

	// Begin printing the body of the table
	try {
		$db = connectToDB();

		// Getting all of the contacts for this user
		$select_stmt = $db->prepare("SELECT * FROM `contacts` WHERE ownerID = :userID");
		$select_stmt->bindValue(":userID", trim($_SESSION['auth_info']['userID']));
		$select_stmt->execute();
		
		// Looping through data
		while($data_array = $select_stmt->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr>";
			echo "<td>{$data_array['firstName']}</td>";
			echo "<td>{$data_array['lastName']}</td>";
			echo "<td>{$data_array['did']}</td>";
			echo "<td>{$data_array['notes']}</td>";
			echo "</tr>";
		}
	} catch(Exception $e) {
		echo "<div id='error'>Exception caught: " . $e->getMessage() . "</div>";
	}

	// Closing table
	echo '</tbody>
	</table>';
}


/**************************************************
	Entry Point
*/
?>
<!DOCTYPE html>
<html>
<head>
	<title>voipSMS: Contact List</title>
	<link rel="stylesheet" type="text/css" href="css/main.css" />

	<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
	<script src="//cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
	<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" />
	<script>
		// JQuery DataTable stuff
		$(document).ready(function(){
			$('#contacts').DataTable({
				"pageLength": 25
			});
		});
	</script>
</head>
<body>
<?php 
	include_once("header.php");

	// Tell off the user if they're not logged in
	if(isset($_SESSION['auth'])) {
		// We're logged in, clear to print the contacts

		echo '<h3>Contact List</h3>
		<div id="addContacts">
			<a href="addContact.php">Add a contact</a>
		</div>';

		listContacts();		
	} else {
		// User isn't logged in, tell them
		echo '<div id="error">'; 
		echo "Error: You can't add a contact while you're logged out.</div>";
	}

?>
</body>
</html>
