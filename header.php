<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<a class="navbar-brand" href="index.php">VoIPSMS</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
		aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
	    <span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav">
		<?php
		// Test if the user's logged in or not.
		// If they aren't print a login screen
		if(isset($_SESSION['auth'])) {
			// User is logged in
			echo '<li class="nav-item">';
			echo '	<a class="nav-link" href="sms.php">SMS Portal</a>';
			echo '</li>';
			echo '<li class="nav-item">';
			echo '	<a class="nav-link" href="contacts.php">Contacts</a>';
			echo '</li>';
			echo '<li class="nav-item">';
			echo '	<a class="nav-link" href="account.php">My Account</a>';
			echo '</li>';
			echo '<li class="nav-item">';
			echo '	<a class="nav-link" href="logout.php">Logout</a>';
			echo '</li>';
		} else {
			// User is not logged in
			echo '<li class="nav-item">';
			echo '	<a class="nav-link" href="register.php">Register</a>';
			echo '</li>';
			echo '<li class="nav-item">';
			echo '	<a class="nav-link" href="login.php">Login</a>';
			echo '</li>';
		}
		?>
		</ul>
	</div>
</nav>
