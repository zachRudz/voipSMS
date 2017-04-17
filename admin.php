<?php
session_start();
require_once('sql/dbinfo.php');
require_once('sql/dbQueries.php');

/**************************************************
	Display users

	Displays a list of users that the admin can delete at will
	This will be a jquery datatables formatted table.

	For the sake of privacy, they will only be able to see each user's...
		- User ID 
		- Name
		- vms email
		- User type
*/
function displayUsers() {
	echo "<div>";
	echo "<form action='admin.php' method='post'>";

	// Building the table of users
	echo "<table id='admin_userList' class='display'>";
	echo "	<thead>";
	echo "		<tr>";
	echo "			<th>Delete</th>";
	echo "			<th>ID</th>";
	echo "			<th>Name</th>";
	echo "			<th>Email</th>";
	echo "			<th>User type</th>";
	echo "		</tr>";
	echo "	</thead>";
	echo "	<tbody>";

	// Getting all of the users
	try {
		$db = connectToDB();

		// Getting all of the contacts for this user                                
		$select_stmt = $db->prepare("SELECT * FROM `users`");
		$select_stmt->execute();                                                    

		// Looping through users to create the form
		while($data = $select_stmt->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr>";
			echo "<td><input type='checkbox' name='deletedUsers[]' 
				value='{$data['userID']}'</td>";

			echo "<td>{$data['userID']}</td>";
			echo "<td>{$data['name']}</td>";
			echo "<td>{$data['vms_email']}</td>";

			if($data['userType'] == 'U')
				echo "<td>User</td>";
			else if($data['userType'] == 'A')
				echo "<td>Admin</td>";
			else
				echo "<td>???</td>";

			echo "</tr>";
		}
		

	} catch (Exception $e) {
		echo "<div class='error'>Exception caught while getting all of the users: ";
		echo $e->getMessage() . "</div>";
	}

	echo "	</tbody>";
	echo "</table>";

	echo "<input type='submit' name='submit' value='Delete Users' />";
	echo "</form>";
	echo "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>voipSMS</title>
	<link rel="stylesheet" type="text/css" href="css/main.css" />

	<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
	<script src="//cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
	<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" />

	<script>
	// JQuery DataTable stuff
	$(document).ready(function(){
		$('#admin_userList').DataTable({
			"pageLength": 25
		});
	});
	</script>

</head>
<body>
<?php 
	include_once("header.php");

	// Making sure we're logged in
	if(!isset($_SESSION['auth'])) {
		echo "<div class='error'>Error: You must be logged in to visit this page.</div>";
	} else {
		// Making sure we're an admin
		if(!isAdmin($_SESSION['auth_info']['userID'])) {
			echo "<div class='error'>Error: You must be an admin to visit this page.</div>";
		} else {
			/**************************************************
				Admin page

				If we're doing an HTTP GET, show the user list.

				Otherwise, process the form first.
			*/
			if($_SERVER['REQUEST_METHOD'] == "POST") {
				// Test if there's some users to delete
				if(!isset($_POST['deletedUsers'])) {
					echo "<div class='error'>Error: No users selected for deletion.</div>";
				} else {
					// Loop through users and delete them (and all of their dids/contacts).
					deleteUsers($_POST['deletedUsers']);
				}
			}

			// Display the list of users
			displayUsers();
		}
	}
?>
</body>
</html>
