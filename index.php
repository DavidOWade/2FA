<?php

require_once('handleForms.php');

/*
Stuff to add:
 - Form nonces
 - Password/email validator
 - Forgot password
*/
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
	<script type="text/javascript" src="assets/js/script.js"></script>
	<title>2FA App</title>
</head>
<body>
	<div id="frontForm" class="largeCenterBox" style="display: initial;">
		<h2>Really super secure app*</h2>
		<hr style="border: 1px solid #534340;">
		<br>
		<ul>
			<li id="login" onclick="switchLoginTab('login')">Login</li>
			<li id="signup" onclick="switchLoginTab('signup')">Sign up</li>
		</ul>
		<br>
		<div id="loginForm" style="display: initial;">
			<input type="text" id="username" placeholder="Username">
			<br>
			<p>
                <input id="password" type="password" id="password" placeholder="Password">
                <i onclick="togglePassword('password')" class="bi bi-eye-slash" id="togglePassword"></i>
            </p>
            <?php
	            if (isset($_GET['verifySuccess'])) {
	            	if ($_GET['verifySuccess'] == 'true') {
	        			echo '<p>Your email verification succeeded. You may now log in.</p>';
	        		} else {
	        			echo '<p>Your email verification failed. Please try again.</p>';
	        		}
	            }
            ?>
			<button onclick="ajax('login')" id="submitLogin">Login</button>
		</div>
		<div id="signupForm" style="display: none;">
			<input type="text" id="emailSignup" placeholder="Email">
			<br><br>
			<input type="text" id="usernameSignup" id="Username" placeholder="Username">
			<br><br>
			<input type="text" id="phone" placeholder="Phone number">
			<br>
			<p>
                <input id="passwordSignup1" type="password" name="passwordSignup1" placeholder="Password">
                <i onclick="togglePassword('passwordSignup1')" class="bi bi-eye-slash" id="togglePaasswordSignup1"></i>
            </p>
			<p>
                <input id="passwordSignup2" type="password" name="passwordSignup2" placeholder="Confirm password">
                <i onclick="togglePassword('passwordSignup2')" class="bi bi-eye-slash" id="togglePasswordSignup2"></i>
            </p>
			<p id="verifyEmailMessage" style="display: none;"></p>
			<br><br>
			<button onclick="ajax('signup')" id="submitSignup">Sign up</button>
		</div>
		<p id="errorMessage" style="color: red;"></p>
	</div>
	<div id="welcome" class="largeCenterBox" style="display: none;">
		<h2>Welcome!</h2>
		<hr style="border: 1px solid #534340;">
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
		<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
		<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
		<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
	</div>
	<div id="verifyDevice" class="largeCenterBox" style="display: none;">
		<h2>Verify device</h2>
		<hr style="border: 1px solid #534340;">
		<p>You are logging in with a device that is not yet verified</p>
		<p>A 6-digit one-time-passcode has been sent to you via SMS. Enter your passcode:</p>
		<input type="text" id="otp" placeholder="------">
		<br><br>
		<button onclick="ajax('verifyOtp')">Verify</button>
	</div>
</body>
</html>

