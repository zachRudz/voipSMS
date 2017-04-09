<?php
/**************************************************
	Logout

	Destroy those spicey session variables
*/
session_start();
$_SESSION = array();
session_destroy();
header("Location: index.php");
exit();
?>
