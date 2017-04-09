<div id="banner">
	<div id="bannerLogo">
		<a href="index.php">VoIP SMS</a>
	</div>

	<?php
	// Test if the user's logged in or not.
	// If they aren't print a login screen
	if(isset($_SESSION['auth'])) {
		// User is logged in
		echo '<span id="bannerGreeting">Hello, '; 
		echo htmlspecialchars($_SESSION['auth_info']['name'], ENT_QUOTES);
		echo '</span>';
		
		echo '<a href="logout.php">Logout</a>';
		echo '<a href="sms.php">SMS Portal</a>';
	} else {
		// User is not logged in
		echo '<form action="login.php" method="POST">
			<span>Login: </span>
		
			<span>Email</span>
			<input name="vms_email" />
		
			<span>Password </span>
			<input type="password" name="userPassword" />

			<input type="submit">
		</form>
		
		<a href="register.php">Register</a>';
	}
	?>
</div>
