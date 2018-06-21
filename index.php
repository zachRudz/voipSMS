<?php session_start();
require_once("sql/dbQueries.php");

/**************************************************
	Write Logged Out Message

	Let the user know about voipSMS.
	Link them to the register page.
*/
function writeLoggedOutMessage() {
	echo "<div class='container py-2 my-2 rounded'>";
	echo '<p>
		<a href="http://voip.ms/">voip.ms</a> offers a great API to compliment their VOIP service.
		In addition, they also offer an SMS service, which also fully interfaces with their API. 
	</p>

	<p>
		voipSMS is an interface to the voip.ms API. 
		You can send and recieve SMS (text) messages using voipSMS, with a cleaner, more functional interface than voip.ms\' built-in SMS center. Some of the benefits of this site include...
		<ul>
			<li>Support for a contacts list.</li>
			<li>Easy switching between DIDs when messaging.</li>
			<li>Searching for conversations.</li>
			<li>FOSS!</li>
		</ul>
	</p>
	<p>
		<a href="register.php">Register</a> today! Or,
		<a href="https://github.com/zachRudz/voipSMS">check this project out on github</a>!
	</p> ';
	echo "</div>";
}

/**************************************************
	Write Logged In Message

	
*/
function writeLoggedInMessage() {
	echo "<div class='container py-2 my-2 rounded'>";
	echo '<p>
		Welcome back, friendo.
	</p>
	<h3>Quick links</h3>
	<ul>
		<li><a href="contactList.php">Contact List</a></li>';
	
	// Link to admin page if we're an admin
	$user = getUser($_SESSION['auth_info']['userID']);
	if($user['userType'] == 'A')
		echo "<li><a href='admin.php'>Admin page</a></li>";

	echo '</ul>
	<h3>VoIP.ms links</h3>
	<ul>
		<li><a href="http://voip.ms">voip.ms</a></li>
	</ul>';
	echo "</div>";
}



/**************************************************
	Entry Point
*/
require_once("pageTop.php")
?>
	<title>voipSMS</title>
</head>
<body>
<?php 
	include_once("header.php");

	// If the user is logged out, write the greeting message
	// Otherwise, write the SMS center in the <body class=background-color:#c9c9c9' of the page
	if(!isset($_SESSION['auth'])) {
		writeLoggedOutMessage();
	} else {
		writeLoggedInMessage();
	}
?>
</<body class=background-color:#c9c9c9'>
<?php require_once("pageBottom.php"); ?>
</html>
